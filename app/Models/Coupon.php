<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    /** Discount only the first payment (or first period of a subscription). */
    public const DURATION_ONCE = 'once';

    /** Discount every cycle of a subscription. Same as "once" for single payments. */
    public const DURATION_FOREVER = 'forever';

    protected $fillable = [
        'code', 'description', 'type', 'value', 'duration', 'max_redemptions',
        'once_per_user', 'starts_at', 'expires_at', 'active',
    ];

    protected function casts(): array
    {
        return [
            'once_per_user' => 'boolean',
            'active' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function normalize(string $code): string
    {
        return mb_strtoupper(trim($code));
    }

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = self::normalize($value);
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function confirmedRedemptions(): HasMany
    {
        return $this->redemptions()->whereNotNull('confirmed_at');
    }

    public function appliesTo(Plan $plan): bool
    {
        $planIds = $this->relationLoaded('plans') ? $this->plans->pluck('id') : $this->plans()->pluck('plans.id');

        return $planIds->isEmpty() || $planIds->contains($plan->id);
    }

    /** Price in cents after applying the coupon once. */
    public function discount(int $cents): int
    {
        $discounted = $this->type === self::TYPE_PERCENT
            ? (int) round($cents * (100 - min(100, $this->value)) / 100)
            : $cents - $this->value;

        return max(0, $discounted);
    }

    public function formattedValue(): string
    {
        return $this->type === self::TYPE_PERCENT
            ? $this->value.' %'
            : number_format($this->value / 100, 2, ',', '.').' EUR';
    }
}
