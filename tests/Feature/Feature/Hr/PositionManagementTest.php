<?php

use App\Models\Company;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('hr user can view positions index', function () {
    $user = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $response = $this->actingAs($user)->get(route('hr.positions.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Hr/Positions/Index')
        ->has('positions')
        ->has('filters')
        ->has('companies'));
});

test('hr user can view create position page', function () {
    $user = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $response = $this->actingAs($user)->get(route('hr.positions.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Hr/Positions/Create')
        ->has('companies')
        ->has('technologies'));
});

test('hr user can create a position', function () {
    $user = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);
    $technology = Technology::factory()->create();

    $response = $this->actingAs($user)->post(route('hr.positions.store'), [
        'title' => 'Senior Laravel Developer',
        'short_description' => 'Looking for an experienced Laravel developer',
        'long_description' => 'We are seeking a senior Laravel developer to join our team...',
        'company_id' => $company->id,
        'seniority' => 'senior',
        'salary_min' => 80000,
        'salary_max' => 120000,
        'remote_type' => 'global',
        'status' => 'published',
        'is_featured' => false,
        'is_external' => false,
        'allow_platform_applications' => true,
        'technology_ids' => [$technology->id],
    ]);

    $response->assertRedirect(route('hr.positions.index'));
    $this->assertDatabaseHas('positions', [
        'title' => 'Senior Laravel Developer',
        'company_id' => $company->id,
        'created_by_user_id' => $user->id,
    ]);
});

test('hr user can view position details', function () {
    $user = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $position = \App\Models\Position::factory()->create([
        'company_id' => $company->id,
        'created_by_user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get(route('hr.positions.show', $position));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Hr/Positions/Show')
        ->has('position'));
});

test('hr user can view dashboard with stats', function () {
    $user = User::factory()->create(['role' => 'hr']);
    $company = Company::factory()->create();
    $user->companies()->attach($company->id, ['role' => 'owner', 'joined_at' => now()]);

    $response = $this->actingAs($user)->get(route('hr.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Hr/Dashboard')
        ->has('stats')
        ->where('stats.total_positions', 0)
        ->where('stats.published_positions', 0)
        ->where('stats.draft_positions', 0));
});

test('developer cannot access hr routes', function () {
    $user = User::factory()->create(['role' => 'developer']);

    $this->actingAs($user)->get(route('hr.dashboard'))->assertForbidden();
    $this->actingAs($user)->get(route('hr.positions.index'))->assertForbidden();
});

test('unauthenticated user cannot access hr routes', function () {
    $this->get(route('hr.dashboard'))->assertRedirect(route('login'));
    $this->get(route('hr.positions.index'))->assertRedirect(route('login'));
});
