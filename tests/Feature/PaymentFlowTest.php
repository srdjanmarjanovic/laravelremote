<?php

use App\Enums\ListingType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Company;
use App\Models\Payment;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

describe('Payment Flow', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);
    });

    it('allows HR users to view payment page for draft position', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->draft()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        $response = get(route('hr.positions.payment', $position));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Hr/Positions/Payment')
            ->has('position')
            ->has('pricing')
        );
    });

    it('prevents viewing payment page for already published position', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        $response = get(route('hr.positions.payment', $position));

        $response->assertRedirect(route('hr.positions.index'));
    });

    it('creates payment record when initiating checkout', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->draft()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        $response = post(route('hr.positions.payment.checkout', $position), [
            'tier' => 'regular',
        ]);

        assertDatabaseHas('payments', [
            'position_id' => $position->id,
            'user_id' => $this->hrUser->id,
            'tier' => ListingType::Regular->value,
            'type' => PaymentType::Initial->value,
            'status' => PaymentStatus::Pending->value,
        ]);
    });

    it('processes initial payment and publishes position in local environment', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->draft()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        $response = post(route('hr.positions.payment.checkout', $position), [
            'tier' => 'featured',
        ]);

        // In local environment, payment auto-completes
        if (app()->environment('local')) {
            $response->assertRedirect(route('hr.positions.index'));

            $position->refresh();
            expect($position->status)->toBe('published')
                ->and($position->listing_type)->toBe(ListingType::Featured)
                ->and($position->paid_at)->not->toBeNull()
                ->and($position->expires_at)->not->toBeNull();

            assertDatabaseHas('payments', [
                'position_id' => $position->id,
                'status' => PaymentStatus::Completed->value,
            ]);
        }
    });

    it('allows upgrading position tier', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'listing_type' => ListingType::Regular,
            'expires_at' => now()->addDays(15),
        ]);

        $response = post(route('hr.positions.payment.upgrade', $position), [
            'tier' => 'featured',
        ]);

        // In local environment, upgrade auto-completes
        if (app()->environment('local')) {
            $response->assertRedirect(route('hr.positions.index'));

            $position->refresh();
            expect($position->listing_type)->toBe(ListingType::Featured);

            assertDatabaseHas('payments', [
                'position_id' => $position->id,
                'type' => PaymentType::Upgrade->value,
                'tier' => ListingType::Featured->value,
                'status' => PaymentStatus::Completed->value,
            ]);
        }
    });

    it('prevents downgrading position tier', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'listing_type' => ListingType::Featured,
        ]);

        $response = post(route('hr.positions.payment.upgrade', $position), [
            'tier' => 'regular',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('prevents upgrading to same tier', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'listing_type' => ListingType::Featured,
        ]);

        $response = post(route('hr.positions.payment.upgrade', $position), [
            'tier' => 'featured',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    });

    it('calculates prorated upgrade price correctly', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'listing_type' => ListingType::Regular,
            'expires_at' => now()->addDays(15), // 15 days remaining
        ]);

        $paymentService = app(\App\Services\Payment\PositionPaymentService::class);
        $upgradePrice = $paymentService->calculateUpgradePrice($position, ListingType::Featured);

        // Regular: $49, Featured: $99
        // Difference: $50
        // Prorated: $50 * (15/30) = $25
        expect($upgradePrice)->toBe(25.0);
    });

    it('creates payment record with correct amount for upgrade', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'listing_type' => ListingType::Regular,
            'expires_at' => now()->addDays(15),
        ]);

        post(route('hr.positions.payment.upgrade', $position), [
            'tier' => 'featured',
        ]);

        assertDatabaseHas('payments', [
            'position_id' => $position->id,
            'amount' => 25.0,
            'tier' => ListingType::Featured->value,
        ]);
    });
});

describe('Payment History', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);
    });

    it('displays payment history for HR user', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        Payment::factory()->count(3)->create([
            'position_id' => $position->id,
            'user_id' => $this->hrUser->id,
        ]);

        $response = get(route('hr.payments.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Hr/Payments/Index')
            ->has('payments.data', 3)
        );
    });

    it('filters payments by status', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        Payment::factory()->completed()->create([
            'position_id' => $position->id,
            'user_id' => $this->hrUser->id,
        ]);
        Payment::factory()->pending()->create([
            'position_id' => $position->id,
            'user_id' => $this->hrUser->id,
        ]);

        $response = get(route('hr.payments.index', ['status' => 'completed']));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('payments.data', 1)
            ->where('payments.data.0.status', PaymentStatus::Completed->value)
        );
    });
});
