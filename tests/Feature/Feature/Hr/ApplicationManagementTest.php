<?php

use App\Models\Application;
use App\Models\Company;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('hr user can view applications index', function () {
    $user = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $response = $this->actingAs($user)->get(route('hr.applications.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Hr/Applications/Index')
        ->has('applications')
        ->has('filters')
        ->has('positions'));
});

test('hr user can view application details', function () {
    $hrUser = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $hrUser->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $position = Position::factory()->create(['company_id' => $company->id]);
    $developer = User::factory()->create(['role' => 'developer']);
    $application = Application::factory()->create([
        'position_id' => $position->id,
        'user_id' => $developer->id,
    ]);

    $response = $this->actingAs($hrUser)->get(route('hr.applications.show', $application));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Hr/Applications/Show')
        ->has('application'));
});

test('hr user can update application status', function () {
    $hrUser = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $hrUser->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $position = Position::factory()->create(['company_id' => $company->id]);
    $developer = User::factory()->create(['role' => 'developer']);
    $application = Application::factory()->create([
        'position_id' => $position->id,
        'user_id' => $developer->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($hrUser)->patch(route('hr.applications.update', $application), [
        'status' => 'reviewing',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('applications', [
        'id' => $application->id,
        'status' => 'reviewing',
        'reviewed_by_user_id' => $hrUser->id,
    ]);
});

test('hr user cannot view applications from other companies', function () {
    $hrUser = User::factory()->create(['role' => 'hr']);
    $myCompany = Company::factory()->create();
    $hrUser->companies()->attach($myCompany->id, ['role' => 'owner', 'joined_at' => now()]);

    $otherCompany = Company::factory()->create();
    $position = Position::factory()->create(['company_id' => $otherCompany->id]);
    $developer = User::factory()->create(['role' => 'developer']);
    $application = Application::factory()->create([
        'position_id' => $position->id,
        'user_id' => $developer->id,
    ]);

    $response = $this->actingAs($hrUser)->get(route('hr.applications.show', $application));

    $response->assertForbidden();
});

test('developer cannot access hr application routes', function () {
    $developer = User::factory()->create(['role' => 'developer']);
    $application = Application::factory()->create();

    $this->actingAs($developer)->get(route('hr.applications.index'))->assertForbidden();
    $this->actingAs($developer)->get(route('hr.applications.show', $application))->assertForbidden();
});

test('admin cannot access hr routes without hr role', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('hr.applications.index'));

    $response->assertForbidden();
});
