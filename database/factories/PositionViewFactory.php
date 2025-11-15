<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PositionView>
 */
class PositionViewFactory extends Factory
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
            'ip_address_hash' => hash('sha256', fake()->ipv4()),
            'country_code' => fake()->countryCode(),
            'city' => fake()->optional()->city(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->optional()->url(),
            'viewed_at' => now()->subDays(fake()->numberBetween(0, 60)),
        ];
    }
}
