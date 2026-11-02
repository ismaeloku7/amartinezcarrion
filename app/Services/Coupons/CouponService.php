<?php

namespace App\Services\Coupons;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\User;

class CouponService
{
    public function find(?string $code): ?Coupon
    {
        $code = $code ? Coupon::normalize($code) : '';

        return $code === '' ? null : Coupon::query()->where('code', $code)->with('plans')->first();
    }

    /** Resolve a code for a plan or explain why it cannot be used. */
    public function apply(?string $code, Plan $plan, ?User $user): Discount
    {
        $coupon = $this->find($code) ?? throw new CouponException(__('Este cupón no existe.'));

        return $this->applyCoupon($coupon, $plan, $user);
    }

    public function applyCoupon(Coupon $coupon, Plan $plan, ?User $user): Discount
    {
        if (! $coupon->active) {
            throw new CouponException(__('Este cupón ya no está activo.'));
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw new CouponException(__('Este cupón aún no está activo.'));
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            throw new CouponException(__('Este cupón ha caducado.'));
        }
        if ($coupon->max_redemptions !== null && $coupon->confirmedRedemptions()->count() >= $coupon->max_redemptions) {
            throw new CouponException(__('Este cupón ya se ha agotado.'));
        }
        if (! $coupon->appliesTo($plan)) {
            throw new CouponException(__('Este cupón no es válido para este plan.'));
        }
        if ($user && $coupon->once_per_user && $coupon->confirmedRedemptions()->where('user_id', $user->id)->exists()) {
            throw new CouponException(__('Ya has usado este cupón.'));
        }

        $discount = Discount::for($coupon, $plan);

        if ($discount->savesCents() <= 0) {
            throw new CouponException(__('Este cupón no es válido para este plan.'));
        }
        if ($plan->isRecurring() && $discount->recurringCents === 0) {
            // PayPal cannot bill a 0 € subscription forever; such access is granted by hand from the panel.
            throw new CouponException(__('Este cupón no es válido para este plan.'));
        }

        return $discount;
    }

    /** Same as apply() but returns null instead of throwing, for price previews. */
    public function preview(?Coupon $coupon, Plan $plan, ?User $user): ?Discount
    {
        if (! $coupon) {
            return null;
        }

        try {
            return $this->applyCoupon($coupon, $plan, $user);
        } catch (CouponException) {
            return null;
        }
    }

    /** Remember the use before sending the user to PayPal; it only counts once confirmed. */
    public function reserve(Discount $discount, User $user, ?string $paypalOrderId = null, ?string $paypalSubscriptionId = null): CouponRedemption
    {
        return CouponRedemption::create([
            'coupon_id' => $discount->coupon->id,
            'user_id' => $user->id,
            'plan_id' => $discount->plan->id,
            'paypal_order_id' => $paypalOrderId,
            'paypal_subscription_id' => $paypalSubscriptionId,
            'original_cents' => $discount->originalCents,
            'first_cents' => $discount->firstCents,
            'recurring_cents' => $discount->recurringCents,
        ]);
    }

    public function confirmOrder(string $paypalOrderId, ?Purchase $purchase = null): void
    {
        CouponRedemption::query()->where('paypal_order_id', $paypalOrderId)->whereNull('confirmed_at')
            ->update(['confirmed_at' => now(), 'purchase_id' => $purchase?->id]);
    }

    public function confirmSubscription(string $paypalSubscriptionId): void
    {
        CouponRedemption::query()->where('paypal_subscription_id', $paypalSubscriptionId)->whereNull('confirmed_at')
            ->update(['confirmed_at' => now()]);
    }
}
