<?php

namespace Database\Factories;

use App\Enums\ListingType;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 49, 199),
            'tier' => fake()->randomElement(ListingType::cases()),
            'type' => fake()->randomElement(PaymentType::cases()),
            'provider' => fake()->randomElement(PaymentProvider::cases()),
            'provider_payment_id' => 'test_'.fake()->uuid(),
            'status' => fake()->randomElement(PaymentStatus::cases()),
        ];
    }

    /**
     * Indicate that the payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Completed,
            'provider_payment_id' => 'completed_'.fake()->uuid(),
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Pending,
        ]);
    }

    /**
     * Indicate that the payment is for initial listing.
     */
    public function initial(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PaymentType::Initial,
        ]);
    }

    /**
     * Indicate that the payment is for upgrade.
     */
    public function upgrade(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PaymentType::Upgrade,
        ]);
    }
}
