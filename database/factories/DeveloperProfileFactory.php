<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeveloperProfile>
 */
class DeveloperProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->developer(),
            'summary' => fake()->paragraph(5),
            'cv_path' => 'cvs/'.fake()->uuid().'.pdf',
            'profile_photo_path' => fake()->optional()->imageUrl(),
            'github_url' => 'https://github.com/'.fake()->userName(),
            'linkedin_url' => 'https://linkedin.com/in/'.fake()->userName(),
            'portfolio_url' => fake()->optional()->url(),
            'other_links' => fake()->optional()->passthrough([
                'twitter' => 'https://twitter.com/'.fake()->userName(),
                'blog' => fake()->url(),
            ]),
        ];
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'summary' => null,
            'cv_path' => null,
        ]);
    }
}
