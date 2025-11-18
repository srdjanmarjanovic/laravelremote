<?php

use App\Models\Company;
use App\Models\Position;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

describe('Position Preview', function () {
    beforeEach(function () {
        $this->hrUser = User::factory()->hr()->create();
        $this->company = Company::factory()->create(['created_by_user_id' => $this->hrUser->id]);
        $this->company->users()->attach($this->hrUser, ['role' => 'owner']);

        $this->otherHrUser = User::factory()->hr()->create();
        $this->otherCompany = Company::factory()->create(['created_by_user_id' => $this->otherHrUser->id]);
        $this->otherCompany->users()->attach($this->otherHrUser, ['role' => 'owner']);

        $this->developerUser = User::factory()->developer()->create();
        $this->adminUser = User::factory()->admin()->create();
    });

    it('allows HR users to preview their draft positions', function () {
        actingAs($this->hrUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSuccessful();
        $response->assertViewIs('positions.preview');
        $response->assertViewHas('position');
        $response->assertViewHas('isPreview', true);
        $response->assertSee('Preview Mode');
        $response->assertSee($position->title);
    });

    it('allows HR users to preview their published positions', function () {
        actingAs($this->hrUser);

        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSuccessful();
        $response->assertViewIs('positions.preview');
        $response->assertSee($position->title);
    });

    it('prevents admins from accessing HR preview route directly', function () {
        actingAs($this->adminUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        // Admins are blocked by role:hr middleware even though policy would allow
        $response = get(route('hr.positions.preview', $position));

        $response->assertForbidden();
    });

    it('prevents HR users from previewing positions from other companies', function () {
        actingAs($this->otherHrUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertForbidden();
    });

    it('prevents developers from previewing positions', function () {
        actingAs($this->developerUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertForbidden();
    });

    it('requires authentication to preview positions', function () {
        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertRedirect(route('login'));
    });

    it('shows preview banner for draft positions', function () {
        actingAs($this->hrUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSee('Preview Mode');
        $response->assertSee('This is how your position will appear to candidates');
        $response->assertSee('Edit Position');
    });

    it('displays all position details in preview', function () {
        actingAs($this->hrUser);

        $technologies = Technology::factory()->count(3)->create();

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
            'title' => 'Senior Developer Position',
            'short_description' => 'Short description here',
            'long_description' => '<p>Long description with HTML</p>',
            'seniority' => 'senior',
            'salary_min' => 80000,
            'salary_max' => 120000,
        ]);

        $position->technologies()->attach($technologies);

        $position->customQuestions()->create([
            'question_text' => 'Why do you want to work here?',
            'is_required' => true,
            'order' => 0,
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSee('Senior Developer Position');
        $response->assertSee('Short description here');
        $response->assertSee('Long description with HTML');
        $response->assertSee('Senior');
        $response->assertSee('$80,000');
        $response->assertSee('$120,000');
        $response->assertSee($position->company->name);

        foreach ($technologies as $tech) {
            $response->assertSee($tech->name);
        }

        $response->assertSee('Application Questions');
        $response->assertSee('Why do you want to work here?');
    });

    it('shows disabled apply button in preview mode', function () {
        actingAs($this->hrUser);

        $position = Position::factory()->published()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'allow_platform_applications' => true,
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSee('Apply Now (Preview)');
    });

    it('shows "Not Published" button for draft positions', function () {
        actingAs($this->hrUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSee('Not Published');
    });

    it('shows company information in preview', function () {
        actingAs($this->hrUser);

        $this->company->update([
            'description' => 'We are an amazing company',
            'website' => 'https://example.com',
        ]);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSee($this->company->name);
        $response->assertSee('We are an amazing company');
        $response->assertSee('https://example.com');
        $response->assertSee('Visit Website');
    });

    it('shows position stats in preview', function () {
        actingAs($this->hrUser);

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
            'expires_at' => now()->addDays(30),
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSee('Position Stats');
        $response->assertSee('Applications:');
        $response->assertSee('Expires:');
    });

    it('loads required relationships for preview', function () {
        actingAs($this->hrUser);

        $technologies = Technology::factory()->count(2)->create();

        $position = Position::factory()->create([
            'company_id' => $this->company->id,
            'created_by_user_id' => $this->hrUser->id,
            'status' => 'draft',
        ]);

        $position->technologies()->attach($technologies);

        $position->customQuestions()->create([
            'question_text' => 'Test question',
            'is_required' => false,
            'order' => 0,
        ]);

        $response = get(route('hr.positions.preview', $position));

        $response->assertSuccessful();

        $viewPosition = $response->viewData('position');
        expect($viewPosition->relationLoaded('company'))->toBeTrue();
        expect($viewPosition->relationLoaded('technologies'))->toBeTrue();
        expect($viewPosition->relationLoaded('customQuestions'))->toBeTrue();
    });
});
