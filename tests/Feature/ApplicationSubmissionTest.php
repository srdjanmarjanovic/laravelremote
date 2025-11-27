<?php

use App\Models\Application;
use App\Models\Company;
use App\Models\CustomQuestion;
use App\Models\DeveloperProfile;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

describe('Application Submission - Apply Now Button Visibility', function () {
    beforeEach(function () {
        $this->company = Company::factory()->create();
        $this->position = Position::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
            'published_at' => now(),
            'allow_platform_applications' => true,
            'is_external' => false,
        ]);
    });

    it('shows Apply Now button for unauthenticated visitors', function () {
        $response = get(route('positions.show', $this->position->slug));

        $response->assertSuccessful();
        $response->assertSee('Apply Now');
    });

    it('redirects unauthenticated visitors to login when clicking Apply Now', function () {
        $response = get(route('positions.apply', $this->position));

        $response->assertRedirect(route('login'));
        // Note: The redirect is handled by auth middleware, which doesn't set a session message
    });

    it('shows Apply Now button for developers on platform applications', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        actingAs($developer);
        $response = get(route('positions.show', $this->position->slug));

        $response->assertSuccessful();
        $response->assertSee('Apply Now');
    });

    it('shows Apply Now button for admins on platform applications', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);
        $response = get(route('positions.show', $this->position->slug));

        $response->assertSuccessful();
        $response->assertSee('Apply Now');
    });

    it('shows Edit button for HR users who can manage the position', function () {
        $hrUser = User::factory()->hr()->create();
        $company = Company::factory()->create(['created_by_user_id' => $hrUser->id]);
        $company->users()->attach($hrUser, ['role' => 'owner']);

        $position = Position::factory()->create([
            'company_id' => $company->id,
            'created_by_user_id' => $hrUser->id,
            'status' => 'published',
            'published_at' => now(),
            'allow_platform_applications' => true,
            'is_external' => false,
        ]);

        actingAs($hrUser);
        $response = get(route('positions.show', $position->slug));

        $response->assertSuccessful();
        $response->assertSee('Edit Position');
        $response->assertDontSee('Apply Now');
    });

    it('does not show Apply Now for external positions that are not platform applications', function () {
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
            'published_at' => now(),
            'is_external' => true,
            'external_apply_url' => 'https://example.com/apply',
            'allow_platform_applications' => false,
        ]);

        $response = get(route('positions.show', $position->slug));

        $response->assertSuccessful();
        $response->assertSee('Apply on Company Site');
        $response->assertDontSee('Apply Now');
    });

    it('shows Applications Closed for positions not accepting applications', function () {
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
            'published_at' => now(),
            'allow_platform_applications' => false,
            'is_external' => false,
        ]);

        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        actingAs($developer);
        $response = get(route('positions.show', $position->slug));

        $response->assertSuccessful();
        $response->assertSee('Applications Closed');
    });
});

describe('Application Submission - Form Access', function () {
    beforeEach(function () {
        $this->company = Company::factory()->create();
        $this->position = Position::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
            'published_at' => now(),
            'allow_platform_applications' => true,
            'is_external' => false,
        ]);
    });

    it('allows developers with complete profiles to access apply form', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        actingAs($developer);
        $response = get(route('positions.apply', $this->position));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Positions/Apply')
            ->has('position')
        );
    });

    it('allows admins to access apply form without profile', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);
        $response = get(route('positions.apply', $this->position));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Positions/Apply')
        );
    });

    it('redirects developers without complete profiles to profile edit', function () {
        $developer = User::factory()->developer()->create();
        // No profile created, so profile is incomplete

        actingAs($developer);
        $response = get(route('positions.apply', $this->position));

        $response->assertRedirect(route('developer.profile.edit'));
        $response->assertSessionHas('warning', 'Please complete your profile before applying to positions.');
    });

    it('prevents HR users from accessing apply form', function () {
        $hrUser = User::factory()->hr()->create();

        actingAs($hrUser);
        $response = get(route('positions.apply', $this->position));

        $response->assertRedirect(route('positions.show', $this->position->slug));
        $response->assertSessionHas('error', 'Only developers and admins can apply to positions.');
    });

    it('prevents applying to positions not accepting applications', function () {
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
            'published_at' => now(),
            'allow_platform_applications' => false,
        ]);

        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        actingAs($developer);
        $response = get(route('positions.apply', $position));

        $response->assertRedirect(route('positions.show', $position->slug));
        $response->assertSessionHas('error', 'This position is not accepting applications.');
    });

    it('prevents applying twice to the same position', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        // Create existing application
        Application::factory()->create([
            'position_id' => $this->position->id,
            'user_id' => $developer->id,
        ]);

        actingAs($developer);
        $response = get(route('positions.apply', $this->position));

        $response->assertRedirect(route('positions.show', $this->position->slug));
        $response->assertSessionHas('error', 'You have already applied to this position.');
    });
});

describe('Application Submission - Form Submission', function () {
    beforeEach(function () {
        $this->company = Company::factory()->create();
        $this->position = Position::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'published',
            'published_at' => now(),
            'allow_platform_applications' => true,
            'is_external' => false,
        ]);
    });

    it('allows developers to submit application', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        actingAs($developer);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [],
        ]);

        $response->assertRedirect(route('positions.show', $this->position->slug));
        $response->assertSessionHas('message', 'Your application has been submitted successfully!');

        assertDatabaseHas('applications', [
            'position_id' => $this->position->id,
            'user_id' => $developer->id,
            'status' => 'pending',
        ]);
    });

    it('allows admins to submit application', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [],
        ]);

        $response->assertRedirect(route('positions.show', $this->position->slug));

        assertDatabaseHas('applications', [
            'position_id' => $this->position->id,
            'user_id' => $admin->id,
            'status' => 'pending',
        ]);
    });

    it('requires answers to required custom questions', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        $requiredQuestion = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Why do you want to work here?',
            'is_required' => true,
            'order' => 1,
        ]);

        actingAs($developer);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [
                $requiredQuestion->id => '', // Empty answer to required question
            ],
        ]);

        $response->assertSessionHasErrors("custom_answers.{$requiredQuestion->id}");
    });

    it('submits application with custom question answers', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        $question1 = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Why do you want to work here?',
            'is_required' => true,
            'order' => 1,
        ]);

        $question2 = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Tell us about your experience',
            'is_required' => false,
            'order' => 2,
        ]);

        actingAs($developer);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [
                $question1->id => 'I want to work here because...',
                $question2->id => 'I have 5 years of experience...',
            ],
        ]);

        $response->assertRedirect(route('positions.show', $this->position->slug));

        assertDatabaseHas('applications', [
            'position_id' => $this->position->id,
            'user_id' => $developer->id,
            'status' => 'pending',
        ]);

        $application = Application::where('position_id', $this->position->id)
            ->where('user_id', $developer->id)
            ->first();

        expect($application->custom_answers)->toBe([
            $question1->id => 'I want to work here because...',
            $question2->id => 'I have 5 years of experience...',
        ]);
    });

    it('prevents HR users from submitting applications', function () {
        $hrUser = User::factory()->hr()->create();

        actingAs($hrUser);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [],
        ]);

        $response->assertForbidden();
    });

    it('prevents submitting application twice', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        // Create existing application
        Application::factory()->create([
            'position_id' => $this->position->id,
            'user_id' => $developer->id,
        ]);

        actingAs($developer);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [],
        ]);

        $response->assertForbidden();
    });

    it('validates custom answer max length', function () {
        $developer = User::factory()->developer()->create();
        DeveloperProfile::factory()->create(['user_id' => $developer->id]);

        $question = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'is_required' => true,
        ]);

        actingAs($developer);
        $response = post(route('positions.apply.store', $this->position), [
            'custom_answers' => [
                $question->id => str_repeat('a', 2001), // Exceeds 2000 character limit
            ],
        ]);

        $response->assertSessionHasErrors("custom_answers.{$question->id}");
    });
});
