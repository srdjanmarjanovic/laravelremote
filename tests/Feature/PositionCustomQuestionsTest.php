<?php

use App\Models\Company;
use App\Models\CustomQuestion;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

describe('Position Custom Questions - Creation', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);
    });

    it('creates position with required custom question', function () {
        actingAs($this->hrUser);

        $response = post(route('hr.positions.store'), [
            'title' => 'Test Position',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'company_id' => $this->company->id,
            'remote_type' => 'global',
            'status' => 'draft',
            'listing_type' => \App\Enums\ListingType::Regular,
            'is_external' => false,
            'allow_platform_applications' => true,
            'custom_questions' => [
                [
                    'question_text' => 'Why do you want to work here?',
                    'is_required' => true,
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $position = Position::where('title', 'Test Position')->first();
        expect($position)->not->toBeNull();

        $question = $position->customQuestions->first();
        expect($question)->not->toBeNull();
        expect($question->question_text)->toBe('Why do you want to work here?');
        expect($question->is_required)->toBeTrue();
    });

    it('creates position with optional custom question', function () {
        actingAs($this->hrUser);

        $response = post(route('hr.positions.store'), [
            'title' => 'Test Position 2',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'company_id' => $this->company->id,
            'remote_type' => 'global',
            'status' => 'draft',
            'listing_type' => \App\Enums\ListingType::Regular,
            'is_external' => false,
            'allow_platform_applications' => true,
            'custom_questions' => [
                [
                    'question_text' => 'Tell us about yourself',
                    'is_required' => false,
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $position = Position::where('title', 'Test Position 2')->first();
        $question = $position->customQuestions->first();

        expect($question->is_required)->toBeFalse();
    });

    it('defaults is_required to false when not provided', function () {
        actingAs($this->hrUser);

        $response = post(route('hr.positions.store'), [
            'title' => 'Test Position 3',
            'short_description' => 'Short desc',
            'long_description' => 'Long description',
            'company_id' => $this->company->id,
            'remote_type' => 'global',
            'status' => 'draft',
            'listing_type' => \App\Enums\ListingType::Regular,
            'is_external' => false,
            'allow_platform_applications' => true,
            'custom_questions' => [
                [
                    'question_text' => 'What motivates you?',
                    'order' => 0,
                    // is_required not provided
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $position = Position::where('title', 'Test Position 3')->first();
        $question = $position->customQuestions->first();

        expect($question->is_required)->toBeFalse();
    });
});

describe('Position Custom Questions - Update', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);

        $this->position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'remote_type' => 'global', // Use global to avoid location_restriction requirement
            'is_external' => false, // Ensure not external to avoid external_apply_url requirement
        ]);
    });

    it('updates existing question is_required from false to true', function () {
        $question = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Original question',
            'is_required' => false,
            'order' => 0,
        ]);

        actingAs($this->hrUser);

        $response = put(route('hr.positions.update', $this->position), [
            'title' => $this->position->title,
            'short_description' => $this->position->short_description,
            'long_description' => $this->position->long_description,
            'company_id' => $this->company->id,
            'remote_type' => $this->position->remote_type,
            'location_restriction' => $this->position->location_restriction,
            'status' => $this->position->status,
            'listing_type' => $this->position->listing_type->value,
            'is_external' => $this->position->is_external,
            'allow_platform_applications' => $this->position->allow_platform_applications,
            'custom_questions' => [
                [
                    'id' => $question->id,
                    'question_text' => 'Original question',
                    'is_required' => true, // Changed to true
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $question->refresh();
        expect($question->is_required)->toBeTrue();
    });

    it('updates existing question is_required from true to false', function () {
        $question = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Required question',
            'is_required' => true,
            'order' => 0,
        ]);

        actingAs($this->hrUser);

        $response = put(route('hr.positions.update', $this->position), [
            'title' => $this->position->title,
            'short_description' => $this->position->short_description,
            'long_description' => $this->position->long_description,
            'company_id' => $this->company->id,
            'remote_type' => $this->position->remote_type,
            'location_restriction' => $this->position->location_restriction,
            'status' => $this->position->status,
            'listing_type' => $this->position->listing_type->value,
            'is_external' => $this->position->is_external,
            'allow_platform_applications' => $this->position->allow_platform_applications,
            'custom_questions' => [
                [
                    'id' => $question->id,
                    'question_text' => 'Required question',
                    'is_required' => false, // Changed to false
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $question->refresh();
        expect($question->is_required)->toBeFalse();
    });

    it('defaults is_required to false when not provided during update', function () {
        $question = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Test question',
            'is_required' => true, // Start as true
            'order' => 0,
        ]);

        actingAs($this->hrUser);

        $response = put(route('hr.positions.update', $this->position), [
            'title' => $this->position->title,
            'short_description' => $this->position->short_description,
            'long_description' => $this->position->long_description,
            'company_id' => $this->company->id,
            'remote_type' => $this->position->remote_type,
            'location_restriction' => $this->position->location_restriction,
            'status' => $this->position->status,
            'listing_type' => $this->position->listing_type->value,
            'is_external' => $this->position->is_external,
            'allow_platform_applications' => $this->position->allow_platform_applications,
            'custom_questions' => [
                [
                    'id' => $question->id,
                    'question_text' => 'Test question',
                    // is_required not provided (simulating unchecked checkbox)
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $question->refresh();
        expect($question->is_required)->toBeFalse();
    });

    it('handles multiple questions with mixed required states', function () {
        actingAs($this->hrUser);

        $response = put(route('hr.positions.update', $this->position), [
            'title' => $this->position->title,
            'short_description' => $this->position->short_description,
            'long_description' => $this->position->long_description,
            'company_id' => $this->company->id,
            'remote_type' => $this->position->remote_type,
            'location_restriction' => $this->position->location_restriction,
            'status' => $this->position->status,
            'listing_type' => $this->position->listing_type->value,
            'is_external' => $this->position->is_external,
            'allow_platform_applications' => $this->position->allow_platform_applications,
            'custom_questions' => [
                [
                    'question_text' => 'Required question',
                    'is_required' => true,
                    'order' => 0,
                ],
                [
                    'question_text' => 'Optional question',
                    'is_required' => false,
                    'order' => 1,
                ],
                [
                    'question_text' => 'Another optional question',
                    // is_required not provided
                    'order' => 2,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $this->position->refresh();
        $questions = $this->position->customQuestions;

        expect($questions)->toHaveCount(3);
        expect($questions[0]->is_required)->toBeTrue();
        expect($questions[1]->is_required)->toBeFalse();
        expect($questions[2]->is_required)->toBeFalse();
    });

    it('properly normalizes string boolean values for is_required', function () {
        $question = CustomQuestion::factory()->create([
            'position_id' => $this->position->id,
            'question_text' => 'Test question',
            'is_required' => false,
            'order' => 0,
        ]);

        actingAs($this->hrUser);

        // Simulate form data where booleans might be sent as strings
        $response = put(route('hr.positions.update', $this->position), [
            'title' => $this->position->title,
            'short_description' => $this->position->short_description,
            'long_description' => $this->position->long_description,
            'company_id' => $this->company->id,
            'remote_type' => $this->position->remote_type,
            'location_restriction' => $this->position->location_restriction,
            'status' => $this->position->status,
            'listing_type' => $this->position->listing_type->value,
            'is_external' => $this->position->is_external,
            'allow_platform_applications' => $this->position->allow_platform_applications,
            'custom_questions' => [
                [
                    'id' => $question->id,
                    'question_text' => 'Test question',
                    'is_required' => '1', // String "1" should be converted to true
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $question->refresh();
        expect($question->is_required)->toBeTrue();

        // Now test with string "0" which should be false
        $response = put(route('hr.positions.update', $this->position), [
            'title' => $this->position->title,
            'short_description' => $this->position->short_description,
            'long_description' => $this->position->long_description,
            'company_id' => $this->company->id,
            'remote_type' => $this->position->remote_type,
            'location_restriction' => $this->position->location_restriction,
            'status' => $this->position->status,
            'listing_type' => $this->position->listing_type->value,
            'is_external' => $this->position->is_external,
            'allow_platform_applications' => $this->position->allow_platform_applications,
            'custom_questions' => [
                [
                    'id' => $question->id,
                    'question_text' => 'Test question',
                    'is_required' => '0', // String "0" should be converted to false
                    'order' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('hr.positions.index'));

        $question->refresh();
        expect($question->is_required)->toBeFalse();
    });
});
