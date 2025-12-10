<?php

namespace App\Http\Controllers;

use App\Enums\ListingType;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus as PaymentStatusEnum;
use App\Enums\PaymentType;
use App\Models\Payment;
use App\Models\Position;
use App\Models\User;
use App\Notifications\PositionPublishedNotification;
use App\Notifications\PositionUpgradedNotification;
use App\Services\Payment\PaymentProviderInterface;
use App\Services\Payment\PositionPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        protected PositionPaymentService $paymentService,
        protected ?PaymentProviderInterface $paymentProvider = null
    ) {
        // Resolve payment provider based on config
        $providerName = config('payments.default_provider');
        $providerClass = match ($providerName) {
            'lemon_squeezy' => \App\Services\Payment\LemonSqueezyProvider::class,
            'paddle' => null, // TODO: Implement PaddleProvider
            'creem' => null, // TODO: Implement CreemProvider
            default => null,
        };

        if ($providerClass && class_exists($providerClass)) {
            $this->paymentProvider = app($providerClass);
        }
    }

    /**
     * Show payment selection page after position creation.
     */
    public function show(Position $position): Response|RedirectResponse
    {
        $this->authorize('update', $position);

        // Ensure position is in draft status
        if ($position->status !== 'draft') {
            return redirect()->route('hr.positions.index')
                ->with('error', 'Position has already been published.');
        }

        $pricing = [
            ListingType::Regular->value => config('payments.pricing.regular'),
            ListingType::Featured->value => config('payments.pricing.featured'),
            ListingType::Top->value => config('payments.pricing.top'),
        ];

        return Inertia::render('Hr/Positions/Payment', [
            'position' => $position->load(['company', 'technologies']),
            'pricing' => $pricing,
        ]);
    }

    /**
     * Initiate checkout for a position payment.
     */
    public function checkout(Request $request, Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $request->validate([
            'tier' => ['required', 'string', 'in:regular,featured,top'],
        ]);

        // Ensure position is in draft status
        if ($position->status !== 'draft') {
            return redirect()->route('hr.positions.index')
                ->with('error', 'Position has already been published.');
        }

        $tier = ListingType::from($request->tier);
        $amount = $this->paymentService->getPriceForTier($tier);

        // Create pending payment record
        $payment = $this->paymentService->createPaymentRecord(
            position: $position,
            userId: auth()->id(),
            amount: $amount,
            tier: $tier,
            type: PaymentType::Initial,
            provider: PaymentProvider::from(config('payments.default_provider'))->value,
            status: PaymentStatusEnum::Pending
        );

        // Use payment provider if available, otherwise simulate for local dev
        // if ($this->paymentProvider && app()->environment('production')) {
        if ($this->paymentProvider) {
            try {
                $checkout = $this->paymentProvider->createCheckout($position, $tier->value);
                // Store payment ID in session for webhook matching
                session(['pending_payment_id' => $payment->id]);

                return redirect($checkout->url);
            } catch (\Exception $e) {
                \Log::error('Payment checkout failed', ['error' => $e->getMessage()]);

                return redirect()->back()
                    ->with('error', 'Failed to initiate payment. Please try again.');
            }
        }

        // Simulate payment completion for local development
        // if (app()->environment('local')) {
        //     return $this->handlePaymentSuccess($payment);
        // }

        return redirect()->route('hr.positions.index')
            ->with('error', 'Payment provider not configured.');
    }

    /**
     * Handle payment upgrade request.
     */
    public function upgrade(Request $request, Position $position): RedirectResponse
    {
        $this->authorize('update', $position);

        $request->validate([
            'tier' => ['required', 'string', 'in:regular,featured,top'],
        ]);

        $newTier = ListingType::from($request->tier);

        // Check if upgrade is allowed
        if (! $this->paymentService->canUpgradeTo($position, $newTier)) {
            return redirect()->back()
                ->with('error', 'Upgrade to this tier is not allowed.');
        }

        // Calculate prorated price
        $amount = $this->paymentService->calculateUpgradePrice($position, $newTier);

        // Create pending payment record
        $payment = $this->paymentService->createPaymentRecord(
            position: $position,
            userId: auth()->id(),
            amount: $amount,
            tier: $newTier,
            type: PaymentType::Upgrade,
            provider: PaymentProvider::from(config('payments.default_provider'))->value,
            status: PaymentStatusEnum::Pending
        );

        // Use payment provider if available, otherwise simulate for local dev
        if ($this->paymentProvider && app()->environment('production')) {
            try {
                $checkout = $this->paymentProvider->createCheckout($position, $newTier->value);
                // Store payment ID in session for webhook matching
                session(['pending_payment_id' => $payment->id]);

                return redirect($checkout->url);
            } catch (\Exception $e) {
                \Log::error('Payment upgrade checkout failed', ['error' => $e->getMessage()]);

                return redirect()->back()
                    ->with('error', 'Failed to initiate upgrade payment. Please try again.');
            }
        }

        // Simulate payment completion for local development
        if (app()->environment('local')) {
            return $this->handlePaymentSuccess($payment);
        }

        return redirect()->route('hr.positions.index')
            ->with('error', 'Payment provider not configured.');
    }

    /**
     * Handle payment provider webhook.
     */
    public function webhook(Request $request): \Illuminate\Http\Response
    {
        if (! $this->paymentProvider) {
            \Log::warning('Webhook received but no payment provider configured');

            return response('No provider configured', 400);
        }

        try {
            $result = $this->paymentProvider->handleWebhook($request);

            if (! $result->success) {
                \Log::error('Webhook processing failed', [
                    'message' => $result->message,
                    'data' => $result->data,
                ]);

                return response('Webhook processing failed', 400);
            }

            // Process successful payment
            if ($result->status === 'completed' && $result->paymentId) {
                $this->processWebhookPayment($result);
            }

            return response('OK', 200);
        } catch (\Exception $e) {
            \Log::error('Webhook exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response('Internal error', 500);
        }
    }

    /**
     * Process payment from webhook result.
     */
    protected function processWebhookPayment(\App\Services\Payment\WebhookResult $result): void
    {
        $data = $result->data;
        $positionId = $data['position_id'] ?? null;
        $orderId = $result->paymentId;

        if (! $positionId) {
            \Log::error('Webhook missing position_id', ['data' => $data]);

            return;
        }

        $position = Position::find($positionId);
        if (! $position) {
            \Log::error('Position not found for webhook', ['position_id' => $positionId]);

            return;
        }

        // Find or create payment record
        $payment = Payment::where('position_id', $positionId)
            ->where('provider_payment_id', $orderId)
            ->first();

        if (! $payment) {
            // Try to find pending payment for this position
            $payment = Payment::where('position_id', $positionId)
                ->where('status', PaymentStatusEnum::Pending)
                ->latest()
                ->first();
        }

        if (! $payment) {
            \Log::error('Payment record not found for webhook', [
                'position_id' => $positionId,
                'order_id' => $orderId,
            ]);

            return;
        }

        DB::transaction(function () use ($payment, $result) {
            $payment->update([
                'status' => PaymentStatusEnum::Completed,
                'provider_payment_id' => $result->paymentId,
            ]);

            $position = $payment->position;

            if ($payment->type === PaymentType::Initial) {
                $this->paymentService->processInitialPayment($position, $payment);
            } else {
                $this->paymentService->processUpgradePayment($position, $payment);
            }

            // Send notifications to admin
            $this->sendAdminNotifications($position, $payment);
        });
    }

    /**
     * Handle payment success callback (redirect from payment provider).
     */
    public function success(Request $request, Position $position): RedirectResponse
    {
        // Payment should be processed via webhook, but handle redirect case
        $payment = Payment::where('position_id', $position->id)
            ->where('status', PaymentStatusEnum::Pending)
            ->latest()
            ->first();

        if ($payment && $payment->status === PaymentStatusEnum::Completed) {
            return redirect()->route('hr.positions.index')
                ->with('success', 'Payment completed successfully. Your position has been published!');
        }

        // If payment not yet processed, show pending message
        return redirect()->route('hr.positions.index')
            ->with('info', 'Payment is being processed. You will be notified when it completes.');
    }

    /**
     * Handle successful payment (for development/testing).
     */
    protected function handlePaymentSuccess(Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => PaymentStatusEnum::Completed,
                'provider_payment_id' => 'dev_'.uniqid(),
            ]);

            $position = $payment->position;

            if ($payment->type === PaymentType::Initial) {
                $this->paymentService->processInitialPayment($position, $payment);
            } else {
                $this->paymentService->processUpgradePayment($position, $payment);
            }

            // Send notifications to admin
            $this->sendAdminNotifications($position, $payment);
        });

        return redirect()->route('hr.positions.index')
            ->with('success', 'Payment completed successfully. Your position has been published!');
    }

    /**
     * Send notifications to admin users about payment events.
     */
    protected function sendAdminNotifications(Position $position, Payment $payment): void
    {
        $admins = User::where('role', 'admin')->get();

        if ($admins->isEmpty()) {
            return;
        }

        if ($payment->type === \App\Enums\PaymentType::Initial) {
            Notification::send($admins, new PositionPublishedNotification($position, $payment));
        } else {
            // Get previous tier from position's listing_type before update
            $previousTier = $position->getOriginal('listing_type') ?? $position->listing_type->value;
            Notification::send($admins, new PositionUpgradedNotification($position, $payment, $previousTier));
        }
    }
}
