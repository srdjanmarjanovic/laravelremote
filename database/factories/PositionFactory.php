<?php

namespace Database\Factories;

use App\Enums\ListingType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Senior Laravel Developer',
            'Full Stack Developer',
            'Frontend Developer',
            'Backend Engineer',
            'DevOps Engineer',
            'Lead Software Engineer',
            'PHP Developer',
            'Vue.js Developer',
            'React Developer',
            'Software Architect',
        ]);

        $publishedAt = fake()->boolean(80) ? now()->subDays(fake()->numberBetween(1, 30)) : null;
        $expiresAt = $publishedAt ? now()->addDays(fake()->numberBetween(30, 90)) : null;

        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'short_description' => fake()->sentence(15),
            'long_description' => fake()->paragraphs(5, true),
            'company_id' => \App\Models\Company::factory(),
            'created_by_user_id' => \App\Models\User::factory()->hr(),
            'seniority' => fake()->randomElement(['junior', 'mid', 'senior', 'lead', 'principal']),
            'salary_min' => fake()->numberBetween(50000, 100000),
            'salary_max' => fake()->numberBetween(100000, 200000),
            'remote_type' => fake()->randomElement(['global', 'timezone', 'country']),
            'location_restriction' => fake()->optional()->country(),
            'status' => $publishedAt ? 'published' : 'draft',
            'listing_type' => ListingType::Regular,
            'is_external' => fake()->boolean(10),
            'external_apply_url' => fake()->boolean(10) ? fake()->url() : null,
            'allow_platform_applications' => fake()->boolean(90),
            'expires_at' => $expiresAt,
            'published_at' => $publishedAt,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'expires_at' => now()->addDays(fake()->numberBetween(30, 90)),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => ListingType::Featured,
        ]);
    }

    public function top(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => ListingType::Top,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
