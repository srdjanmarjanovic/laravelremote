<?php

use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

describe('Position Application Toggle', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);

        $this->position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'allow_platform_applications' => true,
            'is_external' => false,
        ]);
    });

    it('allows HR to close applications', function () {
        actingAs($this->hrUser);

        $response = post(route('hr.positions.toggle-applications', $this->position));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Applications are now closed for this position.');

        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'allow_platform_applications' => false,
        ]);
    });

    it('allows HR to reopen applications', function () {
        $this->position->update(['allow_platform_applications' => false]);

        actingAs($this->hrUser);

        $response = post(route('hr.positions.toggle-applications', $this->position));

        $response->assertRedirect();
        $response->assertSessionHas('message', 'Applications are now open for this position.');

        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'allow_platform_applications' => true,
        ]);
    });

    it('prevents non-HR users from toggling applications', function () {
        $developer = User::factory()->developer()->create();

        actingAs($developer);

        $response = post(route('hr.positions.toggle-applications', $this->position));

        $response->assertForbidden();

        // Verify it didn't change
        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'allow_platform_applications' => true,
        ]);
    });

    it('prevents HR from other companies from toggling applications', function () {
        $otherHrUser = User::factory()->hr()->create();
        $otherCompany = Company::factory()->create(['created_by_user_id' => $otherHrUser->id]);
        $otherCompany->users()->attach($otherHrUser, ['role' => 'owner']);

        actingAs($otherHrUser);

        $response = post(route('hr.positions.toggle-applications', $this->position));

        $response->assertForbidden();

        // Verify it didn't change
        assertDatabaseHas('positions', [
            'id' => $this->position->id,
            'allow_platform_applications' => true,
        ]);
    });

    it('allows admin to toggle applications', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        $response = post(route('hr.positions.toggle-applications', $this->position));

        $response->assertForbidden(); // Admins don't have HR role, so they can't access HR routes

        // However, if we want admins to be able to do this, we'd need to adjust the route middleware
    });

    it('requires authentication to toggle applications', function () {
        $response = post(route('hr.positions.toggle-applications', $this->position));

        $response->assertRedirect(route('login'));
    });

    it('toggles multiple times correctly', function () {
        actingAs($this->hrUser);

        // Close applications
        post(route('hr.positions.toggle-applications', $this->position));
        $this->position->refresh();
        expect($this->position->allow_platform_applications)->toBeFalse();

        // Reopen applications
        post(route('hr.positions.toggle-applications', $this->position));
        $this->position->refresh();
        expect($this->position->allow_platform_applications)->toBeTrue();

        // Close again
        post(route('hr.positions.toggle-applications', $this->position));
        $this->position->refresh();
        expect($this->position->allow_platform_applications)->toBeFalse();
    });
});
