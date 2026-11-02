<?php

namespace App\Http\Controllers;

use App\Models\PaypalSubscription;
use App\Models\Plan;
use App\Services\Coupons\CouponException;
use App\Services\Coupons\CouponService;
use App\Services\PayPal\PayPalBilling;
use App\Services\PayPal\PayPalClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private PayPalBilling $billing, private CouponService $coupons) {}

    /** Send the user to PayPal to approve a plan (subscription or single purchase). */
    public function checkout(Request $request, Plan $plan): RedirectResponse
    {
        abort_unless(PayPalClient::enabled() && $plan->isPurchasable(), 404);
        $user = $request->user();
        $paywall = route('paywall', ['product' => $plan->products->first()?->key ?? 'mdp']);

        $discount = null;
        if ($request->filled('coupon')) {
            try {
                $discount = $this->coupons->apply($request->input('coupon'), $plan, $user);
            } catch (CouponException $e) {
                return redirect()->to($paywall.'?cupon='.urlencode($request->input('coupon')))->with('coupon_error', $e->getMessage());
            }
        }

        if ($discount && ! $plan->isRecurring() && $discount->firstCents === 0) {
            $this->billing->grantFree($user, $plan, $discount);

            return redirect()->route('home')->with('status', __('Cupón aplicado. Ya tienes acceso.'));
        }

        $returnUrl = route('checkout.success', ['plan' => $plan->id]);
        $cancelUrl = route('checkout.cancel');

        try {
            $approveUrl = $plan->isRecurring()
                ? $this->billing->createSubscription($user, $plan, $returnUrl, $cancelUrl, $discount)
                : $this->billing->createOrder($user, $plan, $returnUrl, $cancelUrl, $discount);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->to($paywall)
                ->with('status', __('No se pudo iniciar el pago con PayPal. Inténtalo de nuevo en unos minutos.'));
        }

        return redirect()->away($approveUrl);
    }

    /**
     * PayPal sends the user back here after approving. Orders are captured now;
     * subscriptions are read back from PayPal. Webhooks remain the source of truth.
     */
    public function success(Request $request): RedirectResponse
    {
        $plan = Plan::find($request->integer('plan'));
        $user = $request->user();
        $status = __('Pago completado. Ya tienes acceso.');

        try {
            if ($plan && $plan->isRecurring() && $request->filled('subscription_id')) {
                $subscription = $this->billing->refreshSubscription($request->input('subscription_id'));
                if (! $subscription || $subscription->user_id !== $user->id) {
                    $status = __('No hemos podido localizar tu suscripción. Escríbenos si el cargo se ha realizado.');
                } elseif (! $subscription->isActive()) {
                    $status = __('PayPal está confirmando tu suscripción. El acceso se activará en unos minutos.');
                }
            } elseif ($plan && ! $plan->isRecurring() && $request->filled('token')) {
                $purchase = $this->billing->captureOrder($request->input('token'));
                if (! $purchase || $purchase->user_id !== $user->id) {
                    $status = __('PayPal está confirmando tu pago. El acceso se activará en unos minutos.');
                }
            } else {
                $status = __('PayPal está confirmando tu pago. El acceso se activará en unos minutos.');
            }
        } catch (\Throwable $e) {
            report($e);
            $status = __('PayPal está confirmando tu pago. El acceso se activará en unos minutos.');
        }

        return redirect()->route('home')->with('status', $status);
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('home')->with('status', __('Pago cancelado.'));
    }

    /** Cancel one of the user's own PayPal subscriptions; access lasts until the paid period ends. */
    public function cancelSubscription(Request $request, PaypalSubscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        if ($subscription->isCancellable()) {
            try {
                $this->billing->cancelSubscription($subscription);
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('account')->with('status', __('No se pudo cancelar la suscripción. Inténtalo de nuevo o cancélala desde tu cuenta de PayPal.'));
            }
        }

        return redirect()->route('account')->with('status', __('Suscripción cancelada. Conservas el acceso hasta el final del periodo pagado.'));
    }
}
