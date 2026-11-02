<?php

namespace App\Services\PayPal;

use App\Models\Entitlement;
use App\Models\PaypalSubscription;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AccessService;
use Carbon\Carbon;
use Illuminate\Support\Arr;

/**
 * Everything the portal asks PayPal for: start a payment or subscription, confirm
 * it when the user comes back, and keep entitlements in sync with what PayPal
 * reports (return URL first, webhooks as the source of truth afterwards).
 */
class PayPalBilling
{
    public function __construct(private PayPalClient $client, private AccessService $access) {}

    // ---------------------------------------------------------------- one-off purchases

    /** Create a PayPal order for a single-payment plan and return the approval URL. */
    public function createOrder(User $user, Plan $plan, string $returnUrl, string $cancelUrl): string
    {
        $order = $this->client->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => (string) $plan->id,
                'custom_id' => (string) $user->id,
                'description' => mb_substr($plan->name, 0, 127),
                'amount' => ['currency_code' => $plan->currency, 'value' => self::money($plan->price_cents)],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => [
                'brand_name' => config('paypal.brand_name'),
                'locale' => self::locale(),
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ]]],
        ], PayPalClient::requestId('order-'.$user->id.'-'.$plan->id));

        return self::link($order, ['payer-action', 'approve']);
    }

    /** Capture an approved order and grant access. Returns the purchase, or null when not paid yet. */
    public function captureOrder(string $orderId): ?Purchase
    {
        if ($purchase = Purchase::query()->where('paypal_order_id', $orderId)->first()) {
            return $purchase;
        }

        try {
            $order = $this->client->post("/v2/checkout/orders/{$orderId}/capture", [], PayPalClient::requestId('capture-'.$orderId));
        } catch (PayPalException $e) {
            if ($e->issue() !== 'ORDER_ALREADY_CAPTURED') {
                throw $e;
            }
            $order = $this->client->get("/v2/checkout/orders/{$orderId}");
        }

        return $this->recordOrder($order);
    }

    /** Persist a completed order (from the capture response or a webhook) and grant the plan. */
    public function recordOrder(array $order): ?Purchase
    {
        $unit = $order['purchase_units'][0] ?? [];
        $capture = collect($unit['payments']['captures'] ?? [])->firstWhere('status', 'COMPLETED');

        if (($order['status'] ?? null) !== 'COMPLETED' || ! $capture) {
            return null;
        }

        $user = User::find($unit['custom_id'] ?? $capture['custom_id'] ?? null);
        $plan = Plan::find($unit['reference_id'] ?? null);
        if (! $user || ! $plan) {
            return null;
        }

        $purchase = Purchase::firstOrCreate(
            ['paypal_order_id' => $order['id']],
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'paypal_capture_id' => $capture['id'] ?? null,
                'amount_cents' => self::cents($capture['amount']['value'] ?? null) ?? $plan->price_cents,
                'currency' => strtoupper($capture['amount']['currency_code'] ?? $plan->currency),
                'paid_at' => now(),
            ],
        );

        $this->access->grantPlan($user, $plan, Entitlement::SOURCE_PURCHASE, (string) $purchase->id);

        return $purchase;
    }

    // ---------------------------------------------------------------- subscriptions

    /** Create a PayPal subscription for a recurring plan and return the approval URL. */
    public function createSubscription(User $user, Plan $plan, string $returnUrl, string $cancelUrl): string
    {
        $subscription = $this->client->post('/v1/billing/subscriptions', [
            'plan_id' => $plan->paypal_plan_id,
            'custom_id' => (string) $user->id,
            'subscriber' => ['email_address' => $user->email],
            'application_context' => [
                'brand_name' => config('paypal.brand_name'),
                'locale' => self::locale(),
                'user_action' => 'SUBSCRIBE_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ], PayPalClient::requestId('sub-'.$user->id.'-'.$plan->id));

        PaypalSubscription::updateOrCreate(
            ['paypal_id' => $subscription['id']],
            ['user_id' => $user->id, 'plan_id' => $plan->id, 'paypal_plan_id' => $plan->paypal_plan_id,
                'status' => $subscription['status'] ?? PaypalSubscription::STATUS_APPROVAL_PENDING],
        );

        return self::link($subscription, ['approve']);
    }

    /** Fetch the subscription from PayPal and sync it. */
    public function refreshSubscription(string $paypalId): ?PaypalSubscription
    {
        return $this->syncSubscription($this->client->get("/v1/billing/subscriptions/{$paypalId}"));
    }

    /**
     * Apply a PayPal subscription resource (API response or webhook payload) to our
     * records: ACTIVE grants the plan; anything else closes the access when the paid
     * period ends (or now, if we do not know it).
     */
    public function syncSubscription(array $data): ?PaypalSubscription
    {
        $paypalId = $data['id'] ?? null;
        if (! $paypalId) {
            return null;
        }

        $record = PaypalSubscription::query()->where('paypal_id', $paypalId)->first();
        $user = $record?->user ?? User::find($data['custom_id'] ?? null);
        $plan = $record?->plan ?? Plan::query()->where('paypal_plan_id', $data['plan_id'] ?? '')->first();
        if (! $user || ! $plan) {
            return null;
        }

        $status = $data['status'] ?? $record?->status ?? PaypalSubscription::STATUS_APPROVAL_PENDING;
        $nextBilling = self::time(Arr::get($data, 'billing_info.next_billing_time'));
        $lastPayment = self::time(Arr::get($data, 'billing_info.last_payment.time'));
        $paidThrough = $nextBilling ?? $record?->paid_through_at;

        $record = PaypalSubscription::updateOrCreate(
            ['paypal_id' => $paypalId],
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'paypal_plan_id' => $data['plan_id'] ?? $record?->paypal_plan_id ?? $plan->paypal_plan_id,
                'status' => $status,
                'paid_through_at' => $paidThrough,
                'last_payment_at' => $lastPayment ?? $record?->last_payment_at,
                'cancelled_at' => $status === PaypalSubscription::STATUS_CANCELLED ? ($record?->cancelled_at ?? now()) : null,
            ],
        );

        if ($status === PaypalSubscription::STATUS_ACTIVE) {
            $this->access->grantPlan($user, $plan, Entitlement::SOURCE_SUBSCRIPTION, $paypalId);
        } elseif (in_array($status, [PaypalSubscription::STATUS_CANCELLED, PaypalSubscription::STATUS_EXPIRED], true)) {
            $endsAt = $paidThrough && $paidThrough->isFuture() ? $paidThrough : now();
            $this->access->endSource($user, Entitlement::SOURCE_SUBSCRIPTION, $paypalId, $endsAt);
        } elseif ($status === PaypalSubscription::STATUS_SUSPENDED) {
            $this->access->endSource($user, Entitlement::SOURCE_SUBSCRIPTION, $paypalId, now());
        }

        return $record;
    }

    /** Cancel on PayPal and close the access at the end of the paid period. */
    public function cancelSubscription(PaypalSubscription $subscription, string $reason = 'Cancelled by the user'): PaypalSubscription
    {
        $this->client->post("/v1/billing/subscriptions/{$subscription->paypal_id}/cancel", ['reason' => $reason]);

        try {
            return $this->refreshSubscription($subscription->paypal_id) ?? $subscription;
        } catch (PayPalException $e) {
            report($e);

            return $this->syncSubscription(['id' => $subscription->paypal_id, 'status' => PaypalSubscription::STATUS_CANCELLED]) ?? $subscription;
        }
    }

    // ---------------------------------------------------------------- helpers

    public static function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public static function cents(?string $value): ?int
    {
        return $value === null ? null : (int) round(((float) $value) * 100);
    }

    /** PayPal wants BCP-47 locales such as es-ES; the app only tracks the language. */
    public static function locale(): string
    {
        $locale = str_replace('_', '-', app()->getLocale());

        return match ($locale) {
            'es' => 'es-ES',
            'en' => 'en-US',
            'pt' => 'pt-PT',
            'fr' => 'fr-FR',
            'it' => 'it-IT',
            'de' => 'de-DE',
            default => $locale,
        };
    }

    private static function time(?string $iso): ?Carbon
    {
        return $iso ? Carbon::parse($iso) : null;
    }

    private static function link(array $resource, array $rels): string
    {
        foreach ($rels as $rel) {
            $href = collect($resource['links'] ?? [])->firstWhere('rel', $rel)['href'] ?? null;
            if ($href) {
                return $href;
            }
        }

        throw new \RuntimeException('PayPal no devolvió un enlace de aprobación.');
    }
}
