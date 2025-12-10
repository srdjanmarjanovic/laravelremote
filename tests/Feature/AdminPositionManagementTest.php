<?php

use App\Enums\ListingType;
use App\Enums\PaymentStatus;
use App\Models\Application;
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

describe('Admin Dashboard', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->hrUser = User::factory()->hr()->create();
        $this->developer = User::factory()->developer()->create();
    });

    it('allows admins to access the admin dashboard', function () {
        actingAs($this->admin);

        $response = get(route('admin.dashboard'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('recentPositions')
            ->has('recentApplications')
            ->has('positionsByStatus')
            ->has('applicationsByStatus')
            ->has('paymentsByStatus')
            ->has('monthlyRevenue')
        );
    });

    it('prevents HR users from accessing admin dashboard', function () {
        actingAs($this->hrUser);

        $response = get(route('admin.dashboard'));

        $response->assertForbidden();
    });

    it('prevents developers from accessing admin dashboard', function () {
        actingAs($this->developer);

        $response = get(route('admin.dashboard'));

        $response->assertForbidden();
    });

    it('shows correct statistics on dashboard', function () {
        $publishedPositions = Position::factory()->count(3)->published()->create();
        Position::factory()->count(2)->draft()->create();
        Position::factory()->archived()->create();

        // Create applications for existing positions to avoid creating additional positions
        Application::factory()->count(5)->create([
            'status' => 'pending',
            'position_id' => $publishedPositions->first()->id,
        ]);
        Application::factory()->count(2)->create([
            'status' => 'reviewing',
            'position_id' => $publishedPositions->last()->id,
        ]);

        // Create payment data
        $user = User::factory()->hr()->create();
        Payment::factory()->count(3)->create([
            'status' => PaymentStatus::Completed,
            'amount' => 50.00,
            'user_id' => $user->id,
            'position_id' => $publishedPositions->first()->id,
        ]);
        Payment::factory()->count(2)->create([
            'status' => PaymentStatus::Pending,
            'amount' => 30.00,
            'user_id' => $user->id,
            'position_id' => $publishedPositions->last()->id,
        ]);

        actingAs($this->admin);

        $response = get(route('admin.dashboard'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->where('stats.total_positions', 6)
            ->where('stats.active_positions', 3)
            ->where('stats.total_applications', 7)
            ->where('stats.pending_applications', 5)
            ->where('stats.total_revenue', 150)
            ->where('stats.pending_payments', 60)
            ->where('stats.total_payments', 5)
            ->where('stats.completed_payments', 3)
        );
    });
});

describe('Admin Position Management', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
    });

    it('allows admins to view all positions', function () {
        Position::factory()->count(5)->create(['company_id' => $this->company->id]);

        actingAs($this->admin);

        $response = get(route('admin.positions.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Positions/Index')
            ->has('positions.data', 5)
            ->has('filters')
        );
    });

    it('allows admins to filter positions by status', function () {
        Position::factory()->count(3)->published()->create(['company_id' => $this->company->id]);
        Position::factory()->count(2)->draft()->create(['company_id' => $this->company->id]);

        actingAs($this->admin);

        $response = get(route('admin.positions.index', ['status' => 'published']));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('positions.data', 3)
        );
    });

    it('allows admins to search positions', function () {
        Position::factory()->create(['title' => 'Senior Laravel Developer', 'company_id' => $this->company->id]);
        Position::factory()->create(['title' => 'Junior Vue Developer', 'company_id' => $this->company->id]);

        actingAs($this->admin);

        $response = get(route('admin.positions.index', ['search' => 'Laravel']));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->has('positions.data', 1)
        );
    });

    it('prevents non-admins from accessing admin positions', function () {
        actingAs($this->hrUser);

        $response = get(route('admin.positions.index'));

        $response->assertForbidden();
    });
});

describe('Admin Feature/Unfeature Positions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->hrUser = User::factory()->hr()->create();
        $this->position = Position::factory()->published()->create(['listing_type' => ListingType::Regular]);
    });

    it('allows admins to feature a regular position', function () {
        actingAs($this->admin);

        $response = post(route('admin.positions.feature', $this->position));

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'listing_type' => ListingType::Featured->value,
        ]);
    });

    it('allows admins to unfeature a featured position', function () {
        $this->position->update(['listing_type' => ListingType::Featured]);
        actingAs($this->admin);

        $response = post(route('admin.positions.feature', $this->position));

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'listing_type' => ListingType::Regular->value,
        ]);
    });

    it('prevents HR users from featuring positions', function () {
        actingAs($this->hrUser);

        $response = post(route('admin.positions.feature', $this->position));

        $response->assertForbidden();
    });
});

describe('Admin Archive Positions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->hrUser = User::factory()->hr()->create();
        $this->position = Position::factory()->published()->create();
    });

    it('allows admins to archive any position', function () {
        actingAs($this->admin);

        $response = post(route('admin.positions.archive', $this->position));

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'status' => 'archived',
        ]);
    });

    it('prevents HR users from archiving positions via admin route', function () {
        actingAs($this->hrUser);

        $response = post(route('admin.positions.archive', $this->position));

        $response->assertForbidden();
    });
});

describe('Admin Bulk Actions', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
        $this->hrUser = User::factory()->hr()->create();
        $this->positions = Position::factory()->count(3)->published()->create(['listing_type' => ListingType::Regular]);
    });

    it('allows admins to feature multiple positions', function () {
        actingAs($this->admin);

        $response = post(route('admin.positions.bulk-action'), [
            'action' => 'feature',
            'position_ids' => $this->positions->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        foreach ($this->positions as $position) {
            assertDatabaseHas('positions', [
                'id' => $position->id,
                'listing_type' => ListingType::Featured->value,
            ]);
        }
    });

    it('allows admins to unfeature multiple positions', function () {
        $this->positions->each(fn ($p) => $p->update(['listing_type' => ListingType::Featured]));
        actingAs($this->admin);

        $response = post(route('admin.positions.bulk-action'), [
            'action' => 'unfeature',
            'position_ids' => $this->positions->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        foreach ($this->positions as $position) {
            assertDatabaseHas('positions', [
                'id' => $position->id,
                'listing_type' => ListingType::Regular->value,
            ]);
        }
    });

    it('allows admins to archive multiple positions', function () {
        actingAs($this->admin);

        $response = post(route('admin.positions.bulk-action'), [
            'action' => 'archive',
            'position_ids' => $this->positions->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        foreach ($this->positions as $position) {
            assertDatabaseHas('positions', [
                'id' => $position->id,
                'status' => 'archived',
            ]);
        }
    });

    it('allows admins to delete multiple positions', function () {
        actingAs($this->admin);
        $positionIds = $this->positions->pluck('id')->toArray();

        $response = post(route('admin.positions.bulk-action'), [
            'action' => 'delete',
            'position_ids' => $positionIds,
        ]);

        $response->assertRedirect();
        foreach ($positionIds as $id) {
            expect(Position::find($id))->toBeNull();
        }
    });

    it('validates bulk action request', function () {
        actingAs($this->admin);

        $response = post(route('admin.positions.bulk-action'), [
            'action' => 'invalid_action',
            'position_ids' => [],
        ]);

        $response->assertSessionHasErrors(['action', 'position_ids']);
    });

    it('prevents non-admins from performing bulk actions', function () {
        actingAs($this->hrUser);

        $response = post(route('admin.positions.bulk-action'), [
            'action' => 'feature',
            'position_ids' => $this->positions->pluck('id')->toArray(),
        ]);

        $response->assertForbidden();
    });
});
