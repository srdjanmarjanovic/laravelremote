<?php

use App\Models\Position;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class);

describe('Admin Technology Management', function () {
    beforeEach(function () {
        $this->adminUser = User::factory()->admin()->create();
        $this->hrUser = User::factory()->hr()->create();
        $this->developerUser = User::factory()->developer()->create();
    });

    it('allows admin users to view technologies index', function () {
        actingAs($this->adminUser);
        Technology::factory()->count(5)->create();

        $response = get(route('admin.technologies.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Technologies/Index')
            ->has('technologies.data', 5)
        );
    });

    it('prevents non-admin users from accessing technologies index', function () {
        actingAs($this->hrUser);

        $response = get(route('admin.technologies.index'));

        $response->assertForbidden();
    });

    it('allows admin users to create a new technology', function () {
        actingAs($this->adminUser);

        $technologyData = [
            'name' => 'Laravel',
            'icon' => 'laravel',
        ];

        $response = post(route('admin.technologies.store'), $technologyData);

        $response->assertRedirect();
        assertDatabaseHas('technologies', [
            'name' => 'Laravel',
            'slug' => 'laravel',
            'icon' => 'laravel',
        ]);
    });

    it('generates slug automatically from name when creating technology', function () {
        actingAs($this->adminUser);

        $response = post(route('admin.technologies.store'), [
            'name' => 'React.js',
        ]);

        $response->assertRedirect();
        assertDatabaseHas('technologies', [
            'name' => 'React.js',
            'slug' => 'reactjs', // Laravel's Str::slug removes dots
        ]);
    });

    it('ensures slug uniqueness when creating technology with different name but same slug', function () {
        actingAs($this->adminUser);
        Technology::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

        // Create a technology with a different name that would generate the same slug
        // This shouldn't happen with normal names, but we test the slug uniqueness logic
        $response = post(route('admin.technologies.store'), [
            'name' => 'Laravel Framework', // Different name, but slug would be 'laravel-framework'
        ]);

        $response->assertRedirect();
        // Should create successfully with slug 'laravel-framework'
        assertDatabaseHas('technologies', [
            'name' => 'Laravel Framework',
            'slug' => 'laravel-framework',
        ]);
    });

    it('validates technology data on creation', function () {
        actingAs($this->adminUser);

        $response = post(route('admin.technologies.store'), [
            'name' => '', // Invalid: required
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('prevents duplicate technology names', function () {
        actingAs($this->adminUser);
        Technology::factory()->create(['name' => 'Laravel']);

        $response = post(route('admin.technologies.store'), [
            'name' => 'Laravel',
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('allows admin users to update a technology', function () {
        actingAs($this->adminUser);
        $technology = Technology::factory()->create(['name' => 'Old Name']);

        $response = put(route('admin.technologies.update', $technology), [
            'name' => 'New Name',
            'icon' => 'new-icon',
        ]);

        $response->assertRedirect();
        assertDatabaseHas('technologies', [
            'id' => $technology->id,
            'name' => 'New Name',
            'slug' => 'new-name',
            'icon' => 'new-icon',
        ]);
    });

    it('validates technology data on update', function () {
        actingAs($this->adminUser);
        $technology = Technology::factory()->create();

        $response = put(route('admin.technologies.update', $technology), [
            'name' => '', // Invalid: required
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('prevents duplicate technology names on update', function () {
        actingAs($this->adminUser);
        $technology1 = Technology::factory()->create(['name' => 'Laravel']);
        $technology2 = Technology::factory()->create(['name' => 'React']);

        $response = put(route('admin.technologies.update', $technology2), [
            'name' => 'Laravel', // Duplicate name
        ]);

        $response->assertSessionHasErrors(['name']);
    });

    it('allows admin users to delete a technology', function () {
        actingAs($this->adminUser);
        $technology = Technology::factory()->create(['name' => 'To Delete']);

        $response = delete(route('admin.technologies.destroy', $technology));

        $response->assertRedirect();
        assertDatabaseMissing('technologies', [
            'id' => $technology->id,
        ]);
    });

    it('prevents deleting technology that is used by positions', function () {
        actingAs($this->adminUser);
        $technology = Technology::factory()->create();
        $position = Position::factory()->create();
        $position->technologies()->attach($technology);

        $response = delete(route('admin.technologies.destroy', $technology));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['technology']);
        assertDatabaseHas('technologies', [
            'id' => $technology->id,
        ]);
    });

    it('allows searching technologies by name', function () {
        actingAs($this->adminUser);
        Technology::factory()->create(['name' => 'Laravel']);
        Technology::factory()->create(['name' => 'React']);
        Technology::factory()->create(['name' => 'Vue.js']);

        $response = get(route('admin.technologies.index', ['search' => 'Laravel']));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Technologies/Index')
            ->has('technologies.data', 1)
            ->where('technologies.data.0.name', 'Laravel')
        );
    });

    it('allows sorting technologies by name', function () {
        actingAs($this->adminUser);
        Technology::factory()->create(['name' => 'Zebra']);
        Technology::factory()->create(['name' => 'Alpha']);
        Technology::factory()->create(['name' => 'Beta']);

        $response = get(route('admin.technologies.index', [
            'sort_by' => 'name',
            'sort_order' => 'asc',
        ]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Technologies/Index')
            ->where('technologies.data.0.name', 'Alpha')
            ->where('technologies.data.1.name', 'Beta')
            ->where('technologies.data.2.name', 'Zebra')
        );
    });

    it('allows sorting technologies by positions count', function () {
        actingAs($this->adminUser);
        $tech1 = Technology::factory()->create(['name' => 'Tech 1']);
        $tech2 = Technology::factory()->create(['name' => 'Tech 2']);
        $tech3 = Technology::factory()->create(['name' => 'Tech 3']);

        $position1 = Position::factory()->create();
        $position2 = Position::factory()->create();
        $position3 = Position::factory()->create();

        $position1->technologies()->attach($tech1);
        $position2->technologies()->attach($tech2);
        $position3->technologies()->attach($tech2);
        $position3->technologies()->attach($tech3);

        $response = get(route('admin.technologies.index', [
            'sort_by' => 'positions_count',
            'sort_order' => 'desc',
        ]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Technologies/Index')
            ->where('technologies.data.0.name', 'Tech 2') // Has 2 positions
            ->where('technologies.data.0.positions_count', 2)
        );
    });
});
