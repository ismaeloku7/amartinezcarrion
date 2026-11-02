<?php

namespace App\Services\Coupons;

use App\Models\Coupon;
use App\Models\Plan;

/** A coupon applied to a plan: what the first payment and the following cycles cost. */
final class Discount
{
    public function __construct(
        public readonly Coupon $coupon,
        public readonly Plan $plan,
        public readonly int $originalCents,
        public readonly int $firstCents,
        public readonly int $recurringCents,
    ) {}

    public static function for(Coupon $coupon, Plan $plan): self
    {
        $discounted = $coupon->discount($plan->price_cents);
        // Single payments have no later cycles; "forever" keeps the discount on every cycle.
        $forever = ! $plan->isRecurring() || $coupon->duration === Coupon::DURATION_FOREVER;

        return new self($coupon, $plan, $plan->price_cents, $discounted, $forever ? $discounted : $plan->price_cents);
    }

    public function savesCents(): int
    {
        return $this->originalCents - $this->firstCents;
    }

    public function isFree(): bool
    {
        return $this->firstCents === 0 && $this->recurringCents === 0;
    }

    /** The subscription needs a PayPal plan with different prices from the base plan. */
    public function changesRecurringPlan(): bool
    {
        return $this->plan->isRecurring() && ($this->firstCents !== $this->originalCents || $this->recurringCents !== $this->originalCents);
    }

    public function format(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.').' '.$this->plan->currency;
    }
}
