<?php

namespace App\Services\Payment;

use App\Models\Position;
use Illuminate\Http\Request;

interface PaymentProviderInterface
{
    /**
     * Create a checkout session for a position payment.
     */
    public function createCheckout(Position $position, string $tier): CheckoutSession;

    /**
     * Handle webhook from payment provider.
     */
    public function handleWebhook(Request $request): WebhookResult;

    /**
     * Get payment status from provider.
     */
    public function getPaymentStatus(string $paymentId): PaymentStatus;

    /**
     * Calculate upgrade price (prorated) for upgrading a position tier.
     */
    public function calculateUpgradePrice(Position $position, string $newTier): float;
}

/**
 * Checkout session data structure.
 */
class CheckoutSession
{
    public function __construct(
        public readonly string $url,
        public readonly string $sessionId,
        public readonly array $metadata = []
    ) {}
}

/**
 * Webhook result data structure.
 */
class WebhookResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $paymentId = null,
        public readonly ?string $status = null,
        public readonly ?string $message = null,
        public readonly array $data = []
    ) {}
}

/**
 * Payment status data structure.
 */
class PaymentStatus
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $paymentId = null,
        public readonly ?float $amount = null,
        public readonly array $metadata = []
    ) {}
}
