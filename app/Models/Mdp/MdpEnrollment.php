<?php

namespace App\Models\Mdp;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdpEnrollment extends Model
{
    public const MODE_FULL = 'full';

    public const MODE_LIGHT = 'light';

    public const PHASE_ONE_DAYS = 21;

    protected $fillable = [
        'user_id', 'mode', 'phase', 'rest_weekday', 'reading_time', 'review_times',
        'minimum_alert_time', 'started_at', 'reading_order_position',
    ];

    protected function casts(): array
    {
        return [
            'review_times' => 'array',
            'started_at' => 'immutable_date:Y-m-d',
            'rest_weekday' => 'integer',
            'phase' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(MdpDay::class, 'enrollment_id');
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(MdpWeek::class, 'enrollment_id');
    }

    public function months(): HasMany
    {
        return $this->hasMany(MdpMonth::class, 'enrollment_id');
    }

    public function isFull(): bool
    {
        return $this->mode === self::MODE_FULL;
    }

    public function isRestDay(CarbonInterface $date): bool
    {
        return $date->dayOfWeek === $this->rest_weekday;
    }

    /** Phase 1 covers the first three weeks unless the user fixed it manually. */
    public function phaseOn(CarbonInterface $date): int
    {
        if ($this->phase) {
            return $this->phase;
        }

        return $this->started_at->diffInDays(CarbonImmutable::parse($date), false) >= self::PHASE_ONE_DAYS ? 2 : 1;
    }

    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now($this->user->timezone ?? config('app.timezone'))->startOfDay();
    }
}
