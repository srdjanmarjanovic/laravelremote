<?php

namespace App\Services\Payment;

use App\Enums\ListingType;
use App\Enums\PaymentStatus as PaymentStatusEnum;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Models\Position;
use Carbon\Carbon;

class PositionPaymentService
{
    /**
     * Pricing structure for position tiers.
     */
    private const PRICING = [
        ListingType::Regular->value => 49.00,
        ListingType::Featured->value => 99.00,
        ListingType::Top->value => 199.00,
    ];

    /**
     * Duration in days for all tiers.
     */
    private const DURATION_DAYS = 30;

    /**
     * Get the price for a tier.
     */
    public function getPriceForTier(ListingType $tier): float
    {
        return self::PRICING[$tier->value];
    }

    /**
     * Calculate prorated upgrade price.
     *
     * Formula: (New Tier Price - Current Tier Price) × (Remaining Days / 30)
     */
    public function calculateUpgradePrice(Position $position, ListingType $newTier): float
    {
        $currentTier = $position->listing_type;
        $currentPrice = self::PRICING[$currentTier->value];
        $newPrice = self::PRICING[$newTier->value];

        // If upgrading to same or lower tier, return 0 (shouldn't happen, but safety check)
        if ($newPrice <= $currentPrice) {
            return 0.00;
        }

        // Calculate remaining days
        if (! $position->expires_at) {
            // If no expiration, treat as full period
            return $newPrice - $currentPrice;
        }

        $remainingDays = max(0, Carbon::now()->diffInDays($position->expires_at, false));

        if ($remainingDays <= 0) {
            // Position expired, full price
            return $newPrice - $currentPrice;
        }

        // Prorated calculation
        $proratedAmount = ($newPrice - $currentPrice) * ($remainingDays / self::DURATION_DAYS);

        return round($proratedAmount, 2);
    }

    /**
     * Get remaining days for a position.
     */
    public function getRemainingDays(Position $position): ?int
    {
        if (! $position->expires_at) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($position->expires_at, false));
    }

    /**
     * Process initial payment completion.
     */
    public function processInitialPayment(Position $position, Payment $payment): void
    {
        $position->update([
            'status' => 'published',
            'listing_type' => $payment->tier,
            'published_at' => now(),
            'expires_at' => now()->addDays(self::DURATION_DAYS),
            'paid_at' => now(),
            'payment_id' => $payment->provider_payment_id,
        ]);
    }

    /**
     * Process tier upgrade payment completion.
     */
    public function processUpgradePayment(Position $position, Payment $payment): void
    {
        // Calculate new expiration date based on remaining days
        $remainingDays = $this->getRemainingDays($position) ?? self::DURATION_DAYS;

        $position->update([
            'listing_type' => $payment->tier,
            'expires_at' => now()->addDays($remainingDays),
            'paid_at' => now(),
            'payment_id' => $payment->provider_payment_id,
        ]);
    }

    /**
     * Check if a position can be upgraded to a specific tier.
     */
    public function canUpgradeTo(Position $position, ListingType $newTier): bool
    {
        $currentTier = $position->listing_type;

        // Can't downgrade
        if ($newTier === ListingType::Regular) {
            return false;
        }

        // Can't upgrade to same tier
        if ($currentTier === $newTier) {
            return false;
        }

        // Can upgrade Regular → Featured or Top
        if ($currentTier === ListingType::Regular) {
            return true;
        }

        // Can upgrade Featured → Top
        if ($currentTier === ListingType::Featured && $newTier === ListingType::Top) {
            return true;
        }

        // Can't upgrade Top to anything
        return false;
    }

    /**
     * Create a payment record.
     */
    public function createPaymentRecord(
        Position $position,
        int $userId,
        float $amount,
        ListingType $tier,
        PaymentType $type,
        string $provider,
        ?string $providerPaymentId = null,
        PaymentStatusEnum $status = PaymentStatusEnum::Pending
    ): Payment {
        return Payment::create([
            'position_id' => $position->id,
            'user_id' => $userId,
            'amount' => $amount,
            'tier' => $tier,
            'type' => $type,
            'provider' => $provider,
            'provider_payment_id' => $providerPaymentId,
            'status' => $status,
        ]);
    }
}
