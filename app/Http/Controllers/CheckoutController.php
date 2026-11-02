<?php

namespace App\Http\Controllers;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\Purchase;
use App\Services\AccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;

class CheckoutController extends Controller
{
    public function __construct(private AccessService $access) {}

    /** Send the user to Stripe Checkout for a plan (subscription or single purchase). */
    public function checkout(Request $request, Plan $plan)
    {
        abort_unless($plan->active && $plan->stripe_price_id && filled(config('cashier.secret')), 404);
        $user = $request->user();

        $options = [
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}&plan='.$plan->id,
            'cancel_url' => route('checkout.cancel'),
            'locale' => app()->getLocale(),
        ];

        if ($plan->isRecurring()) {
            $builder = $user->newSubscription($plan->key, $plan->stripe_price_id);
            if ($plan->trial_days > 0) {
                $builder->trialDays($plan->trial_days);
            }

            return $builder->checkout($options);
        }

        return $user->checkout([$plan->stripe_price_id => 1], $options + ['metadata' => ['plan_id' => $plan->id]]);
    }

    /** Stripe sends the user back here; webhooks remain the source of truth, this is a fast path. */
    public function success(Request $request): RedirectResponse
    {
        $plan = Plan::find($request->integer('plan'));
        $user = $request->user();

        if ($plan && $request->filled('session_id')) {
            try {
                $session = Cashier::stripe()->checkout->sessions->retrieve($request->input('session_id'));

                if ($session->payment_status === 'paid' || $session->status === 'complete') {
                    if ($plan->isRecurring()) {
                        $this->access->grantPlan($user, $plan, Entitlement::SOURCE_SUBSCRIPTION, $session->subscription);
                    } else {
                        $purchase = Purchase::firstOrCreate(
                            ['stripe_checkout_session_id' => $session->id],
                            ['user_id' => $user->id, 'plan_id' => $plan->id, 'stripe_payment_intent_id' => $session->payment_intent,
                                'amount_cents' => $session->amount_total ?? $plan->price_cents, 'currency' => strtoupper($session->currency ?? $plan->currency), 'paid_at' => now()],
                        );
                        $this->access->grantPlan($user, $plan, Entitlement::SOURCE_PURCHASE, (string) $purchase->id);
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('home')->with('status', __('Pago completado. Ya tienes acceso.'));
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('home')->with('status', __('Pago cancelado.'));
    }

    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('account'));
    }
}
