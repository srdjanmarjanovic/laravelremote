<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'logo' => null,
            'description' => fake()->paragraph(3),
            'website' => fake()->url(),
            'social_links' => [
                'twitter' => 'https://twitter.com/'.fake()->userName(),
                'linkedin' => 'https://linkedin.com/company/'.fake()->slug(),
                'github' => 'https://github.com/'.fake()->userName(),
            ],
            'created_by_user_id' => \App\Models\User::factory(),
        ];
    }
}
