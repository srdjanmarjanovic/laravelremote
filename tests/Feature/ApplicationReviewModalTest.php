<?php

use App\Models\Company;
use App\Models\CustomQuestion;
use App\Models\DeveloperProfile;
use App\Models\Position;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    $this->developer = User::factory()->developer()->create();
    $this->developerProfile = DeveloperProfile::factory()->create([
        'user_id' => $this->developer->id,
        'summary' => 'Experienced Laravel developer with 5 years of experience.',
        'cv_path' => 'cvs/test-cv.pdf',
        'github_url' => 'https://github.com/testuser',
        'linkedin_url' => 'https://linkedin.com/in/testuser',
        'portfolio_url' => 'https://portfolio.test',
    ]);

    $this->company = Company::factory()->create();
    $this->position = Position::factory()->create([
        'company_id' => $this->company->id,
        'status' => 'published',
        'published_at' => now(),
        'allow_platform_applications' => true,
        'is_external' => false,
    ]);
});

it('loads application page with position data for modal', function () {
    actingAs($this->developer);

    $response = get(route('positions.apply', $this->position));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Positions/Apply')
            ->has('position')
            ->where('position.id', $this->position->id)
            ->where('position.title', $this->position->title)
        );
});

it('loads position with custom questions for modal', function () {
    actingAs($this->developer);

    $question1 = CustomQuestion::factory()->create([
        'position_id' => $this->position->id,
        'question_text' => 'Why do you want to work for us?',
        'is_required' => true,
        'order' => 1,
    ]);

    $question2 = CustomQuestion::factory()->create([
        'position_id' => $this->position->id,
        'question_text' => 'What is your salary expectation?',
        'is_required' => false,
        'order' => 2,
    ]);

    $response = get(route('positions.apply', $this->position));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Positions/Apply')
            ->has('position.custom_questions', 2)
            ->where('position.custom_questions.0.question_text', 'Why do you want to work for us?')
            ->where('position.custom_questions.0.is_required', true)
            ->where('position.custom_questions.1.question_text', 'What is your salary expectation?')
            ->where('position.custom_questions.1.is_required', false)
        );
});

it('can submit application without custom questions via modal', function () {
    actingAs($this->developer);

    $response = post(route('positions.apply.store', $this->position), [
        'custom_answers' => [],
    ]);

    $response->assertRedirect(route('positions.show', $this->position->slug))
        ->assertSessionHas('message', 'Your application has been submitted successfully!');

    expect($this->developer->applications()->count())->toBe(1);

    $application = $this->developer->applications()->first();
    expect($application->position_id)->toBe($this->position->id);
    expect($application->status)->toBe('pending');
});

it('can submit application with custom questions answered via modal', function () {
    actingAs($this->developer);

    $question = CustomQuestion::factory()->create([
        'position_id' => $this->position->id,
        'question_text' => 'Why do you want to work for us?',
        'is_required' => true,
        'order' => 1,
    ]);

    $response = post(route('positions.apply.store', $this->position), [
        'custom_answers' => [
            $question->id => 'I am passionate about your mission and values.',
        ],
    ]);

    $response->assertRedirect(route('positions.show', $this->position->slug));

    expect($this->developer->applications()->count())->toBe(1);

    $application = $this->developer->applications()->first();
    expect($application->position_id)->toBe($this->position->id);
    expect($application->custom_answers)->toBeArray();
    expect($application->custom_answers[$question->id])->toBe('I am passionate about your mission and values.');
});

it('validates required custom questions on submission', function () {
    actingAs($this->developer);

    $question = CustomQuestion::factory()->create([
        'position_id' => $this->position->id,
        'question_text' => 'Why do you want to work for us?',
        'is_required' => true,
        'order' => 1,
    ]);

    $response = post(route('positions.apply.store', $this->position), [
        'custom_answers' => [
            $question->id => '',
        ],
    ]);

    $response->assertSessionHasErrors(['custom_answers.'.$question->id]);

    expect($this->developer->applications()->count())->toBe(0);
});

it('prevents applying to position twice', function () {
    actingAs($this->developer);

    // First application
    post(route('positions.apply.store', $this->position), [
        'custom_answers' => [],
    ])->assertRedirect();

    // Try to access apply page again
    $response = get(route('positions.apply', $this->position));

    $response->assertRedirect(route('positions.show', $this->position->slug))
        ->assertSessionHas('error', 'You have already applied to this position.');
});

it('requires complete profile to access application modal', function () {
    $developer = User::factory()->developer()->create();
    // No developer profile created, so profile is incomplete

    actingAs($developer);

    $response = get(route('positions.apply', $this->position));

    $response->assertRedirect(route('developer.profile.edit'))
        ->assertSessionHas('warning', 'Please complete your profile before applying to positions.');
});

it('only allows developers and admins to access application modal', function () {
    $hrUser = User::factory()->hr()->create();

    actingAs($hrUser);

    $response = get(route('positions.apply', $this->position));

    $response->assertRedirect(route('positions.show', $this->position->slug))
        ->assertSessionHas('error', 'Only developers and admins can apply to positions.');
});
