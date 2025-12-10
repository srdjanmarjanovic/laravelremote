<?php

namespace App\Services\Payment;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LemonSqueezyProvider implements PaymentProviderInterface
{
    private string $apiKey;

    private string $storeId;

    private string $webhookSecret;

    private string $apiUrl = 'https://api.lemonsqueezy.com/v1';

    public function __construct()
    {
        $this->apiKey = config('payments.providers.lemon_squeezy.api_key', '');
        $this->storeId = config('payments.providers.lemon_squeezy.store_id', '');
        $this->webhookSecret = config('payments.providers.lemon_squeezy.webhook_secret', '');
    }

    /**
     * Create a checkout session for a position payment.
     */
    public function createCheckout(Position $position, string $tier): CheckoutSession
    {
        $pricing = config("payments.pricing.{$tier}");
        $variantId = $this->getVariantIdForTier($tier);

        if (! $variantId) {
            throw new \RuntimeException("No variant ID configured for tier: {$tier}");
        }

        $successUrl = route('hr.positions.payment.success', ['position' => $position->id]);
        $cancelUrl = route('hr.positions.payment', ['position' => $position->id]);

        // Lemon Squeezy uses JSON:API format
        $payload = [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'custom_price' => (int) ($pricing * 100), // Convert to cents
                    'product_options' => [
                        'name' => 'Position Listing - '.ucfirst($tier).' Tier',
                        'description' => "30-day listing for: {$position->title}",
                    ],
                    'checkout_options' => [
                        'embed' => false,
                        'media' => false,
                        'logo' => false,
                    ],
                    'checkout_data' => [
                        'custom' => [
                            'position_id' => (string) $position->id,
                            'tier' => $tier,
                            'user_id' => (string) auth()->id(),
                        ],
                    ],
                    'expires_at' => now()->addHours(1)->toIso8601String(),
                    'preview' => false,
                    'test_mode' => config('app.env') !== 'production',
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => $this->storeId,
                        ],
                    ],
                    'variant' => [
                        'data' => [
                            'type' => 'variants',
                            'id' => $variantId,
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->post("{$this->apiUrl}/checkouts", $payload);

        if (! $response->successful()) {
            Log::error('Lemon Squeezy checkout creation failed', [
                'response' => $response->json(),
                'status' => $response->status(),
            ]);
            throw new \RuntimeException('Failed to create checkout session');
        }

        $data = $response->json('data');
        $checkoutUrl = $data['attributes']['url'] ?? null;
        $sessionId = $data['id'] ?? null;

        if (! $checkoutUrl || ! $sessionId) {
            throw new \RuntimeException('Invalid checkout response from Lemon Squeezy');
        }

        return new CheckoutSession(
            url: $checkoutUrl,
            sessionId: $sessionId,
            metadata: [
                'checkout_id' => $sessionId,
                'position_id' => $position->id,
                'tier' => $tier,
            ]
        );
    }

    /**
     * Handle webhook from payment provider.
     */
    public function handleWebhook(Request $request): WebhookResult
    {
        $signature = $request->header('X-Signature');

        if (! $this->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Lemon Squeezy webhook signature verification failed');

            return new WebhookResult(
                success: false,
                message: 'Invalid webhook signature'
            );
        }

        $payload = $request->json();
        $eventName = $payload['meta']['event_name'] ?? null;

        // Handle different webhook events
        switch ($eventName) {
            case 'order_created':
            case 'subscription_created':
                return $this->handlePaymentSuccess($payload);
            case 'order_refunded':
                return $this->handlePaymentRefund($payload);
            default:
                Log::info('Unhandled Lemon Squeezy webhook event', ['event' => $eventName]);

                return new WebhookResult(
                    success: true,
                    message: "Event {$eventName} received but not processed"
                );
        }
    }

    /**
     * Get payment status from provider.
     */
    public function getPaymentStatus(string $paymentId): PaymentStatus
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/vnd.api+json',
        ])->get("{$this->apiUrl}/orders/{$paymentId}");

        if (! $response->successful()) {
            return new PaymentStatus(
                status: 'unknown',
                paymentId: $paymentId
            );
        }

        $data = $response->json('data');
        $status = $data['attributes']['status'] ?? 'unknown';
        $amount = ($data['attributes']['total'] ?? 0) / 100; // Convert from cents

        return new PaymentStatus(
            status: $this->mapLemonSqueezyStatus($status),
            paymentId: $paymentId,
            amount: $amount,
            metadata: $data['attributes'] ?? []
        );
    }

    /**
     * Calculate upgrade price (prorated) for upgrading a position tier.
     * This is handled by PositionPaymentService, so we just return 0 here.
     */
    public function calculateUpgradePrice(Position $position, string $newTier): float
    {
        // Pricing calculation is handled by PositionPaymentService
        // This method exists for interface compliance but pricing logic
        // is centralized in PositionPaymentService
        return 0.0;
    }

    /**
     * Verify webhook signature.
     */
    private function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature || ! $this->webhookSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle successful payment webhook.
     */
    private function handlePaymentSuccess(array $payload): WebhookResult
    {
        $data = $payload['data'] ?? [];
        $attributes = $data['attributes'] ?? [];
        $orderId = $data['id'] ?? null;

        // Extract custom data from order items
        $orderItems = $attributes['order_items'] ?? [];
        $customData = [];

        foreach ($orderItems as $item) {
            $itemCustom = $item['product_options']['checkout_data']['custom'] ?? [];
            if (! empty($itemCustom)) {
                $customData = $itemCustom;
                break;
            }
        }

        // Fallback: try to get from included relationships
        if (empty($customData) && isset($payload['included'])) {
            foreach ($payload['included'] as $included) {
                if (($included['type'] ?? '') === 'order-items') {
                    $itemCustom = $included['attributes']['product_options']['checkout_data']['custom'] ?? [];
                    if (! empty($itemCustom)) {
                        $customData = $itemCustom;
                        break;
                    }
                }
            }
        }

        $positionId = $customData['position_id'] ?? null;
        $tier = $customData['tier'] ?? null;

        if (! $positionId || ! $tier) {
            Log::error('Lemon Squeezy webhook missing required data', ['payload' => $payload]);

            return new WebhookResult(
                success: false,
                message: 'Missing position_id or tier in webhook payload'
            );
        }

        return new WebhookResult(
            success: true,
            paymentId: $orderId,
            status: 'completed',
            data: [
                'position_id' => $positionId,
                'tier' => $tier,
                'order_id' => $orderId,
                'amount' => ($attributes['total'] ?? 0) / 100,
            ]
        );
    }

    /**
     * Handle payment refund webhook.
     */
    private function handlePaymentRefund(array $payload): WebhookResult
    {
        $orderId = $payload['data']['id'] ?? null;

        return new WebhookResult(
            success: true,
            paymentId: $orderId,
            status: 'refunded',
            data: ['order_id' => $orderId]
        );
    }

    /**
     * Map Lemon Squeezy order status to our payment status.
     */
    private function mapLemonSqueezyStatus(string $status): string
    {
        return match ($status) {
            'paid' => 'completed',
            'pending' => 'pending',
            'failed' => 'failed',
            'refunded' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Get variant ID for a tier.
     * These should be configured in the payments config or environment.
     */
    private function getVariantIdForTier(string $tier): ?string
    {
        return config("payments.providers.lemon_squeezy.variants.{$tier}");
    }
}
