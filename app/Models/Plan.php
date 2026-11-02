<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Plan extends Model
{
    use HasTranslations;

    public const INTERVAL_MONTH = 'month';

    public const INTERVAL_YEAR = 'year';

    public const INTERVAL_ONCE = 'once';

    protected $fillable = [
        'key', 'name', 'description', 'paypal_plan_id', 'paypal_product_id', 'interval',
        'price_cents', 'currency', 'trial_days', 'active', 'position',
    ];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function isRecurring(): bool
    {
        return $this->interval !== self::INTERVAL_ONCE;
    }

    /** Single payments only need a price; subscriptions also need the matching PayPal plan (P-…). */
    public function isPurchasable(): bool
    {
        return $this->active && $this->price_cents > 0 && (! $this->isRecurring() || filled($this->paypal_plan_id));
    }

    public function formattedPrice(): string
    {
        return number_format($this->price_cents / 100, 2, ',', '.').' '.$this->currency;
    }
}
