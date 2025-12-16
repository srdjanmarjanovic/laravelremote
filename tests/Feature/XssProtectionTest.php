<?php

use App\Models\Company;
use App\Models\CustomQuestion;
use App\Models\DeveloperProfile;
use App\Models\Position;
use App\Models\User;
use App\Services\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('strips HTML tags from plain text fields', function () {
    $input = '<script>alert("XSS")</script>Hello <b>World</b>';
    $result = HtmlSanitizer::stripHtml($input);

    expect($result)->toBe('Hello World')
        ->not->toContain('<script>')
        ->not->toContain('<b>')
        ->not->toContain('alert');
});

it('sanitizes rich text by allowing safe HTML tags', function () {
    $input = '<p>Hello <strong>World</strong></p><script>alert("XSS")</script><a href="https://example.com">Link</a>';
    $result = HtmlSanitizer::sanitizeRichText($input);

    expect($result)
        ->toContain('<p>')
        ->toContain('<strong>')
        ->toContain('<a')
        ->not->toContain('<script>')
        ->not->toContain('alert("XSS")');
});

it('removes dangerous attributes from rich text', function () {
    $input = '<a href="javascript:alert(\'XSS\')">Click</a><img src="x" onerror="alert(\'XSS\')">';
    $result = HtmlSanitizer::sanitizeRichText($input);

    expect($result)
        ->not->toContain('javascript:')
        ->not->toContain('onerror=');
});

it('sanitizes position title on creation', function () {
    $hr = User::factory()->hr()->create();
    $company = Company::factory()->create(['created_by_user_id' => $hr->id]);

    $response = $this->actingAs($hr)->post(route('hr.positions.store'), [
        'title' => '<script>alert("XSS")</script>Developer',
        'short_description' => 'Short description',
        'long_description' => '<p>Description</p>',
        'company_id' => $company->id,
        'remote_type' => 'global',
        'allow_platform_applications' => true,
    ]);

    $response->assertRedirect();
    $position = Position::where('title', 'Developer')->first();
    expect($position)->not->toBeNull();
    expect($position->title)->toBe('Developer')
        ->not->toContain('<script>');
});

it('sanitizes position long description allowing safe HTML', function () {
    $hr = User::factory()->hr()->create();
    $company = Company::factory()->create(['created_by_user_id' => $hr->id]);

    $response = $this->actingAs($hr)->post(route('hr.positions.store'), [
        'title' => 'Developer',
        'short_description' => 'Short description',
        'long_description' => '<p>Hello <strong>World</strong></p><script>alert("XSS")</script>',
        'company_id' => $company->id,
        'remote_type' => 'global',
        'allow_platform_applications' => true,
    ]);

    $response->assertRedirect();
    $position = Position::where('title', 'Developer')->first();
    expect($position)->not->toBeNull();
    expect($position->long_description)
        ->toContain('<p>')
        ->toContain('<strong>')
        ->not->toContain('<script>')
        ->not->toContain('alert("XSS")');
});

it('sanitizes custom question text', function () {
    $hr = User::factory()->hr()->create();
    $company = Company::factory()->create(['created_by_user_id' => $hr->id]);
    $position = Position::factory()->create([
        'company_id' => $company->id,
        'created_by_user_id' => $hr->id,
    ]);

    $response = $this->actingAs($hr)->put(route('hr.positions.update', $position), [
        'title' => $position->title,
        'short_description' => $position->short_description,
        'long_description' => $position->long_description,
        'company_id' => $company->id,
        'remote_type' => $position->remote_type,
        'allow_platform_applications' => $position->allow_platform_applications,
        'custom_questions' => [
            [
                'question_text' => '<script>alert("XSS")</script>Why do you want this job?',
                'is_required' => true,
                'order' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();
    $question = CustomQuestion::where('position_id', $position->id)->first();
    expect($question)->not->toBeNull();
    expect($question->question_text)
        ->toBe('Why do you want this job?')
        ->not->toContain('<script>');
});

it('sanitizes custom answers in applications', function () {
    $hr = User::factory()->hr()->create();
    $company = Company::factory()->create(['created_by_user_id' => $hr->id]);
    $position = Position::factory()->published()->create([
        'company_id' => $company->id,
        'created_by_user_id' => $hr->id,
        'allow_platform_applications' => true,
    ]);
    $question = CustomQuestion::factory()->create([
        'position_id' => $position->id,
        'is_required' => true,
    ]);
    $developer = User::factory()->developer()->create();
    DeveloperProfile::factory()->create(['user_id' => $developer->id]);

    $response = $this->actingAs($developer)->post(route('positions.apply.store', $position), [
        'custom_answers' => [
            $question->id => '<script>alert("XSS")</script>I am a great developer!',
        ],
    ]);

    $response->assertRedirect();
    $application = $position->applications()->where('user_id', $developer->id)->first();
    expect($application)->not->toBeNull();
    $answer = $application->custom_answers[$question->id];
    expect($answer)
        ->toBe('I am a great developer!')
        ->not->toContain('<script>');
});

it('sanitizes developer profile summary', function () {
    $developer = User::factory()->developer()->create();
    DeveloperProfile::factory()->create(['user_id' => $developer->id]);

    $response = $this->actingAs($developer)->patch(route('profile.update'), [
        'name' => $developer->name,
        'email' => $developer->email,
        'developer_profile' => [
            'summary' => '<script>alert("XSS")</script>I am a full-stack developer with 5 years of experience.',
        ],
    ]);

    $response->assertRedirect();
    $profile = $developer->fresh()->developerProfile;
    expect($profile)->not->toBeNull();
    expect($profile->summary)
        ->toBe('I am a full-stack developer with 5 years of experience.')
        ->not->toContain('<script>');
});

it('sanitizes company name and description', function () {
    $hr = User::factory()->hr()->create();

    $response = $this->actingAs($hr)->post(route('hr.company.store'), [
        'name' => '<script>alert("XSS")</script>Tech Corp',
        'description' => '<b>Great</b> company with <script>alert("XSS")</script>amazing culture.',
    ]);

    $response->assertRedirect();
    $company = Company::where('name', 'Tech Corp')->first();
    expect($company)->not->toBeNull();
    expect($company->name)->toBe('Tech Corp')
        ->not->toContain('<script>');
    expect($company->description)
        ->toBe('Great company with amazing culture.')
        ->not->toContain('<script>')
        ->not->toContain('<b>');
});

it('removes javascript protocol from links in rich text', function () {
    $input = '<a href="javascript:alert(\'XSS\')">Click</a><a href="https://example.com">Safe</a>';
    $result = HtmlSanitizer::sanitizeRichText($input);

    expect($result)
        ->not->toContain('javascript:alert')
        ->toContain('https://example.com');
});

it('removes event handlers from rich text', function () {
    $input = '<p onclick="alert(\'XSS\')" onerror="alert(\'XSS\')">Text</p>';
    $result = HtmlSanitizer::sanitizeRichText($input);

    expect($result)
        ->toContain('<p')
        ->toContain('Text</p>')
        ->not->toContain('onclick=')
        ->not->toContain('onerror=');
});
