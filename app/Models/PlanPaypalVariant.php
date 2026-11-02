<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A PayPal plan (P-…) derived from a portal plan with coupon prices. */
class PlanPaypalVariant extends Model
{
    protected $fillable = ['plan_id', 'first_cents', 'recurring_cents', 'paypal_plan_id'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
