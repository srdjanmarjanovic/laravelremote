<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => \App\Models\Position::factory(),
            'user_id' => \App\Models\User::factory()->developer(),
            'cover_letter' => fake()->paragraph(4),
            'custom_answers' => null,
            'status' => 'pending',
            'reviewed_by_user_id' => null,
            'applied_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewed_by_user_id' => null,
        ]);
    }

    public function reviewing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reviewing',
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'reviewed_by_user_id' => \App\Models\User::factory()->hr(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewed_by_user_id' => \App\Models\User::factory()->hr(),
        ]);
    }
}
