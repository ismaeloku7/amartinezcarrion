<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mirror of a PayPal billing subscription (I-…), kept in sync from the return URL
 * and from webhooks. Access itself lives in entitlements keyed by the PayPal id.
 */
class PaypalSubscription extends Model
{
    public const STATUS_APPROVAL_PENDING = 'APPROVAL_PENDING';

    public const STATUS_APPROVED = 'APPROVED';

    public const STATUS_ACTIVE = 'ACTIVE';

    public const STATUS_SUSPENDED = 'SUSPENDED';

    public const STATUS_CANCELLED = 'CANCELLED';

    public const STATUS_EXPIRED = 'EXPIRED';

    protected $fillable = [
        'user_id', 'plan_id', 'paypal_id', 'paypal_plan_id', 'status',
        'paid_through_at', 'last_payment_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_through_at' => 'datetime',
            'last_payment_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** Still running on PayPal's side, so the user can cancel it. */
    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_APPROVED], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => __('Activa'),
            self::STATUS_SUSPENDED => __('Suspendida (pago pendiente)'),
            self::STATUS_CANCELLED => __('Cancelada'),
            self::STATUS_EXPIRED => __('Finalizada'),
            default => __('Pendiente de confirmación'),
        };
    }
}
