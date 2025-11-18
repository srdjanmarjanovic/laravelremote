<?php

use App\Models\Application;
use App\Models\Company;
use App\Models\Position;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

describe('Position Management', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);
        $this->developerUser = User::factory()->developer()->create();
    });

    it('allows HR users to view positions list', function () {
        actingAs($this->hrUser);
        Position::factory()->count(3)->create(['company_id' => $this->company->id, 'created_by_user_id' => $this->hrUser->id]);

        $response = get(route('hr.positions.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Hr/Positions/Index')
            ->has('positions.data', 3)
        );
    });

    it('prevents developers from accessing HR position management', function () {
        actingAs($this->developerUser);

        $response = get(route('hr.positions.index'));

        $response->assertForbidden();
    });

    it('allows HR users to create a new position', function () {
        actingAs($this->hrUser);
        $technologies = Technology::factory()->count(3)->create();

        $positionData = [
            'title' => 'Senior Laravel Developer',
            'short_description' => 'We are looking for an experienced Laravel developer.',
            'long_description' => 'Full job description goes here with requirements and responsibilities.',
            'company_id' => $this->company->id,
            'seniority' => 'senior',
            'salary_min' => 80000,
            'salary_max' => 120000,
            'remote_type' => 'global',
            'status' => 'published',
            'is_featured' => false,
            'is_external' => false,
            'allow_platform_applications' => true,
            'technologies' => $technologies->pluck('id')->toArray(),
            'custom_questions' => [
                ['question_text' => 'Why do you want to work for us?', 'is_required' => true, 'order' => 0],
            ],
        ];

        $response = post(route('hr.positions.store'), $positionData);

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'title' => 'Senior Laravel Developer',
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        $position = Position::where('title', 'Senior Laravel Developer')->first();
        expect($position->technologies)->toHaveCount(3);
        expect($position->customQuestions)->toHaveCount(1);
    });

    it('validates position data on creation', function () {
        actingAs($this->hrUser);

        $response = post(route('hr.positions.store'), [
            'title' => '', // Invalid: required
            'short_description' => 'Test',
            'company_id' => $this->company->id,
        ]);

        $response->assertSessionHasErrors(['title', 'long_description']);
    });

    it('allows HR users to update their positions', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'title' => 'Old Title',
        ]);

        $response = post(route('hr.positions.update', $position), [
            'title' => 'Updated Title',
            'short_description' => $position->short_description,
            'long_description' => $position->long_description,
            'company_id' => $this->company->id,
            'status' => $position->status,
            'remote_type' => $position->remote_type,
            'allow_platform_applications' => true,
            '_method' => 'PUT',
        ]);

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'id' => $position->id,
            'title' => 'Updated Title',
        ]);
    });

    it('prevents HR users from updating positions from other companies', function () {
        actingAs($this->hrUser);
        $otherCompany = Company::factory()->create();
        $position = Position::factory()->create(['company_id' => $otherCompany->id]);

        $response = post(route('hr.positions.update', $position), [
            'title' => 'Hacked Title',
            '_method' => 'PUT',
        ]);

        $response->assertForbidden();
    });

    it('allows HR users to archive their positions', function () {
        actingAs($this->hrUser);
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'published',
        ]);

        $response = post(route('hr.positions.archive', $position));

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'id' => $position->id,
            'status' => 'archived',
        ]);
    });

    it('allows admins to feature positions', function () {
        $admin = User::factory()->admin()->create();
        actingAs($admin);
        $position = Position::factory()->create();

        $response = post(route('admin.positions.feature', $position));

        $response->assertRedirect();
        assertDatabaseHas('positions', [
            'id' => $position->id,
            'is_featured' => true,
        ]);
    });
});

describe('Position Applications', function () {
    beforeEach(function () {
        $this->developer = User::factory()->developer()->create();
        $this->developer->developerProfile()->create([
            'summary' => 'Experienced developer',
            'cv_path' => 'cvs/test.pdf',
        ]);
        $this->position = Position::factory()->published()->create();
    });

    it('allows developers with complete profiles to apply', function () {
        actingAs($this->developer);

        $response = post(route('positions.apply.store', $this->position), [
            'cover_letter' => 'I am very interested in this position.',
            'custom_answers' => [],
        ]);

        $response->assertRedirect();
        assertDatabaseHas('applications', [
            'position_id' => $this->position->id,
            'user_id' => $this->developer->id,
            'status' => 'pending',
        ]);
    });

    it('prevents developers from applying twice to the same position', function () {
        actingAs($this->developer);
        Application::factory()->create([
            'position_id' => $this->position->id,
            'user_id' => $this->developer->id,
        ]);

        $response = post(route('positions.apply.store', $this->position), [
            'cover_letter' => 'Another application',
        ]);

        $response->assertForbidden();
    });

    it('prevents developers without complete profiles from applying', function () {
        $incompleteUser = User::factory()->developer()->create();
        $incompleteUser->developerProfile()->create(['summary' => null, 'cv_path' => null]);
        actingAs($incompleteUser);

        $response = get(route('positions.apply', $this->position));

        $response->assertRedirect(route('developer.profile.edit'));
    });

    it('allows HR to view applications for their positions', function () {
        $hrUser = User::factory()->hr()->create();
        $company = Company::factory()->create(['created_by_user_id' => $hrUser->id]);
        $company->users()->attach($hrUser, ['role' => 'owner']);
        $position = Position::factory()->create(['company_id' => $company->id, 'created_by_user_id' => $hrUser->id]);
        Application::factory()->count(3)->create(['position_id' => $position->id]);

        actingAs($hrUser);
        $response = get(route('hr.applications.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Hr/Applications/Index')
            ->has('applications.data', 3)
        );
    });

    it('allows HR to update application status', function () {
        $hrUser = User::factory()->hr()->create();
        $company = Company::factory()->create(['created_by_user_id' => $hrUser->id]);
        $company->users()->attach($hrUser, ['role' => 'owner']);
        $position = Position::factory()->create(['company_id' => $company->id, 'created_by_user_id' => $hrUser->id]);
        $application = Application::factory()->create(['position_id' => $position->id, 'status' => 'pending']);

        actingAs($hrUser);
        $response = post(route('hr.applications.update', $application), [
            'status' => 'reviewing',
            '_method' => 'PATCH',
        ]);

        $response->assertRedirect();
        assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'reviewing',
            'reviewed_by_user_id' => $hrUser->id,
        ]);
    });
});

describe('Public Position Browsing', function () {
    it('displays published positions on public index', function () {
        Position::factory()->published()->count(5)->create();
        Position::factory()->draft()->count(2)->create();

        $response = get(route('positions.index'));

        $response->assertSuccessful();
        $response->assertViewHas('positions');
        expect($response->viewData('positions'))->toHaveCount(5);
    });

    it('filters positions by technology', function () {
        $laravel = Technology::factory()->create(['name' => 'Laravel']);
        $vue = Technology::factory()->create(['name' => 'Vue.js']);

        $laravelPosition = Position::factory()->published()->create();
        $laravelPosition->technologies()->attach($laravel);

        $vuePosition = Position::factory()->published()->create();
        $vuePosition->technologies()->attach($vue);

        $response = get(route('positions.index', ['technologies' => [$laravel->id]]));

        $response->assertSuccessful();
        expect($response->viewData('positions'))->toHaveCount(1);
        expect($response->viewData('positions')->first()->id)->toBe($laravelPosition->id);
    });

    it('filters positions by seniority', function () {
        Position::factory()->published()->create(['seniority' => 'senior']);
        Position::factory()->published()->create(['seniority' => 'junior']);

        $response = get(route('positions.index', ['seniority' => 'senior']));

        $response->assertSuccessful();
        expect($response->viewData('positions'))->toHaveCount(1);
        expect($response->viewData('positions')->first()->seniority)->toBe('senior');
    });

    it('tracks position views anonymously', function () {
        $position = Position::factory()->published()->create();

        $response = get(route('positions.show', $position->slug));

        $response->assertSuccessful();
        assertDatabaseHas('position_views', [
            'position_id' => $position->id,
        ]);
    });
});
