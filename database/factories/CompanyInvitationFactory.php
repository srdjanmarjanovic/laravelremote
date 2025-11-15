<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyInvitation>
 */
class CompanyInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'email' => fake()->safeEmail(),
            'role' => fake()->randomElement(['admin', 'member']),
            'invited_by_user_id' => \App\Models\User::factory()->hr(),
            'token' => \Illuminate\Support\Str::random(32),
            'accepted_at' => null,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(1),
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepted_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
