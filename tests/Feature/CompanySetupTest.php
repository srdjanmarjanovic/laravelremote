<?php

use App\Models\Company;
use App\Models\User;

beforeEach(function () {
    $this->hrUser = User::factory()->create(['role' => 'hr']);
});

describe('Company Setup Page', function () {
    it('redirects new HR users to company setup after selecting role', function () {
        $user = User::factory()->create(['role' => null]);

        $this->actingAs($user)
            ->post('/select-account-type', ['role' => 'hr'])
            ->assertRedirect(route('hr.company.setup'));
    });

    it('allows HR users without a company to view the setup page', function () {
        $this->actingAs($this->hrUser)
            ->get(route('hr.company.setup'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Hr/CompanySetup')
                ->where('company', null)
            );
    });

    it('redirects to edit page when company is already complete', function () {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'description' => 'Test Description',
            'created_by_user_id' => $this->hrUser->id,
        ]);
        $company->users()->attach($this->hrUser->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->hrUser)
            ->get(route('hr.company.setup'))
            ->assertRedirect(route('hr.company.edit'));
    });

    it('allows HR users to access company edit page', function () {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'description' => 'Test Description',
            'created_by_user_id' => $this->hrUser->id,
        ]);
        $company->users()->attach($this->hrUser->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->hrUser)
            ->get(route('hr.company.edit'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Hr/CompanySetup')
                ->where('isEditing', true)
                ->has('company')
            );
    });

    it('redirects to setup when accessing edit without a company', function () {
        $this->actingAs($this->hrUser)
            ->get(route('hr.company.edit'))
            ->assertRedirect(route('hr.company.setup'));
    });

    it('shows setup page for incomplete company profiles', function () {
        $company = Company::factory()->create([
            'name' => 'Test Company',
            'description' => null,
            'created_by_user_id' => $this->hrUser->id,
        ]);
        $company->users()->attach($this->hrUser->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->hrUser)
            ->get(route('hr.company.setup'))
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Hr/CompanySetup')
            );
    });
});

describe('Company Creation', function () {
    it('allows HR users to create a new company', function () {
        $this->actingAs($this->hrUser)
            ->post(route('hr.company.store'), [
                'name' => 'New Company',
                'description' => 'A great company description',
                'website' => 'https://newcompany.com',
                'social_links' => [
                    'twitter' => 'https://twitter.com/newcompany',
                    'linkedin' => 'https://linkedin.com/company/newcompany',
                    'github' => '',
                ],
            ])
            ->assertRedirect(route('hr.dashboard'));

        $this->assertDatabaseHas('companies', [
            'name' => 'New Company',
            'description' => 'A great company description',
            'website' => 'https://newcompany.com',
            'created_by_user_id' => $this->hrUser->id,
        ]);

        // Verify the user is attached to the company
        expect($this->hrUser->fresh()->companies()->count())->toBe(1);
    });

    it('requires company name', function () {
        $this->actingAs($this->hrUser)
            ->post(route('hr.company.store'), [
                'name' => '',
                'description' => 'A description',
            ])
            ->assertSessionHasErrors('name');
    });

    it('requires company description', function () {
        $this->actingAs($this->hrUser)
            ->post(route('hr.company.store'), [
                'name' => 'Company Name',
                'description' => '',
            ])
            ->assertSessionHasErrors('description');
    });

    it('validates website URL format', function () {
        $this->actingAs($this->hrUser)
            ->post(route('hr.company.store'), [
                'name' => 'Company Name',
                'description' => 'Description',
                'website' => 'not-a-url',
            ])
            ->assertSessionHasErrors('website');
    });

    it('generates unique slug for companies with same name', function () {
        Company::factory()->create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'created_by_user_id' => User::factory()->create(['role' => 'hr'])->id,
        ]);

        $this->actingAs($this->hrUser)
            ->post(route('hr.company.store'), [
                'name' => 'Test Company',
                'description' => 'A description',
            ])
            ->assertRedirect(route('hr.dashboard'));

        $this->assertDatabaseHas('companies', [
            'slug' => 'test-company-1',
        ]);
    });
});

describe('Company Update', function () {
    beforeEach(function () {
        $this->company = Company::factory()->create([
            'name' => 'Original Company',
            'description' => 'Original description',
            'created_by_user_id' => $this->hrUser->id,
        ]);
        $this->company->users()->attach($this->hrUser->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);
    });

    it('allows HR users to update their company', function () {
        $this->actingAs($this->hrUser)
            ->put(route('hr.company.update'), [
                'name' => 'Updated Company',
                'description' => 'Updated description',
            ])
            ->assertRedirect(route('hr.dashboard'));

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'name' => 'Updated Company',
            'description' => 'Updated description',
        ]);
    });

    it('updates slug when company name changes', function () {
        $this->actingAs($this->hrUser)
            ->put(route('hr.company.update'), [
                'name' => 'Brand New Name',
                'description' => 'Description',
            ])
            ->assertRedirect(route('hr.dashboard'));

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'slug' => 'brand-new-name',
        ]);
    });
});

describe('Position Creation Restriction', function () {
    it('blocks position creation when company profile is incomplete', function () {
        $this->actingAs($this->hrUser)
            ->get(route('hr.positions.create'))
            ->assertRedirect(route('hr.company.setup'));
    });

    it('allows position creation when company profile is complete', function () {
        $company = Company::factory()->create([
            'name' => 'Complete Company',
            'description' => 'A complete description',
            'created_by_user_id' => $this->hrUser->id,
        ]);
        $company->users()->attach($this->hrUser->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $this->actingAs($this->hrUser)
            ->get(route('hr.positions.create'))
            ->assertSuccessful();
    });

    it('blocks position index when company profile is incomplete', function () {
        $this->actingAs($this->hrUser)
            ->get(route('hr.positions.index'))
            ->assertRedirect(route('hr.company.setup'));
    });
});

describe('Company Model', function () {
    it('checks if company profile is complete', function () {
        $incompleteCompany = Company::factory()->create([
            'name' => 'Test Company',
            'description' => null,
            'created_by_user_id' => User::factory()->create(['role' => 'hr'])->id,
        ]);

        $completeCompany = Company::factory()->create([
            'name' => 'Test Company',
            'description' => 'A description',
            'created_by_user_id' => User::factory()->create(['role' => 'hr'])->id,
        ]);

        expect($incompleteCompany->isComplete())->toBeFalse();
        expect($completeCompany->isComplete())->toBeTrue();
    });
});

describe('User Model', function () {
    it('checks if HR user has complete company profile', function () {
        expect($this->hrUser->hasCompleteCompanyProfile())->toBeFalse();

        $company = Company::factory()->create([
            'name' => 'Test Company',
            'description' => 'A description',
            'created_by_user_id' => $this->hrUser->id,
        ]);
        $company->users()->attach($this->hrUser->id, [
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        expect($this->hrUser->fresh()->hasCompleteCompanyProfile())->toBeTrue();
    });

    it('returns true for non-HR users when checking company profile', function () {
        $developer = User::factory()->create(['role' => 'developer']);

        expect($developer->hasCompleteCompanyProfile())->toBeTrue();
    });
});
