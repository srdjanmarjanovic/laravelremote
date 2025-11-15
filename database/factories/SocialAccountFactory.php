<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(['github', 'google', 'linkedin']);

        return [
            'user_id' => \App\Models\User::factory(),
            'provider' => $provider,
            'provider_id' => fake()->unique()->numerify('##########'),
        ];
    }

    public function github(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'github',
        ]);
    }

    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'google',
        ]);
    }

    public function linkedin(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'linkedin',
        ]);
    }
}
