<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One use of a coupon. Created when the user is sent to PayPal (pending) and
 * confirmed when PayPal reports the payment, so abandoned checkouts do not count.
 */
class CouponRedemption extends Model
{
    protected $fillable = [
        'coupon_id', 'user_id', 'plan_id', 'purchase_id', 'paypal_order_id', 'paypal_subscription_id',
        'original_cents', 'first_cents', 'recurring_cents', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return ['confirmed_at' => 'datetime'];
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
