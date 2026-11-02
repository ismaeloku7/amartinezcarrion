<?php

namespace App\Listeners;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AccessService;
use Carbon\Carbon;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Stripe webhooks are the source of truth for access: subscriptions grant the plan's
 * products while active and close them when they end; one-off checkouts create a purchase.
 */
class SyncEntitlementsFromStripe
{
    public function __construct(private AccessService $access) {}

    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $object = $payload['data']['object'] ?? [];

        match ($payload['type'] ?? null) {
            'customer.subscription.created', 'customer.subscription.updated' => $this->syncSubscription($object),
            'customer.subscription.deleted' => $this->endSubscription($object),
            'checkout.session.completed' => $this->completeCheckout($object),
            default => null,
        };
    }

    private function syncSubscription(array $subscription): void
    {
        $user = $this->userFor($subscription['customer'] ?? null);
        if (! $user) {
            return;
        }

        $priceIds = collect($subscription['items']['data'] ?? [])->pluck('price.id')->filter()->all();
        $plans = Plan::query()->whereIn('stripe_price_id', $priceIds)->with('products')->get();
        $active = in_array($subscription['status'] ?? '', ['active', 'trialing', 'past_due'], true);
        $endsAt = ($subscription['cancel_at_period_end'] ?? false) && isset($subscription['current_period_end'])
            ? Carbon::createFromTimestamp($subscription['current_period_end'])
            : null;

        foreach ($plans as $plan) {
            if ($active) {
                $this->access->grantPlan($user, $plan, Entitlement::SOURCE_SUBSCRIPTION, $subscription['id'], $endsAt);
            } else {
                $this->access->endSource($user, Entitlement::SOURCE_SUBSCRIPTION, $subscription['id'], now());
            }
        }
    }

    private function endSubscription(array $subscription): void
    {
        $user = $this->userFor($subscription['customer'] ?? null);
        if ($user) {
            $this->access->endSource($user, Entitlement::SOURCE_SUBSCRIPTION, $subscription['id'], now());
        }
    }

    private function completeCheckout(array $session): void
    {
        if (($session['mode'] ?? '') !== 'payment') {
            return; // subscriptions are handled by the subscription events
        }

        $user = $this->userFor($session['customer'] ?? null);
        $plan = Plan::find($session['metadata']['plan_id'] ?? null);
        if (! $user || ! $plan) {
            return;
        }

        $purchase = Purchase::firstOrCreate(
            ['stripe_checkout_session_id' => $session['id']],
            ['user_id' => $user->id, 'plan_id' => $plan->id, 'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
                'amount_cents' => $session['amount_total'] ?? $plan->price_cents, 'currency' => strtoupper($session['currency'] ?? $plan->currency), 'paid_at' => now()],
        );

        $this->access->grantPlan($user, $plan, Entitlement::SOURCE_PURCHASE, (string) $purchase->id);
    }

    private function userFor(?string $stripeId): ?User
    {
        return $stripeId ? User::query()->where('stripe_id', $stripeId)->first() : null;
    }
}
