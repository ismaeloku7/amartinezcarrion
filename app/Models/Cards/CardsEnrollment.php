<?php

namespace App\Models\Cards;

use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardsEnrollment extends Model
{
    protected $fillable = [
        'user_id', 'rest_weekday', 'morning_time', 'review_times', 'evening_time', 'started_at',
        'unlocked_until', 'last_group_reviewed', 'tracking_enabled', 'tracking_prompted_at',
    ];

    protected function casts(): array
    {
        return [
            'review_times' => 'array',
            'started_at' => 'immutable_date:Y-m-d',
            'tracking_enabled' => 'boolean',
            'tracking_prompted_at' => 'datetime',
            'rest_weekday' => 'integer',
            'unlocked_until' => 'integer',
            'last_group_reviewed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function days(): HasMany
    {
        return $this->hasMany(CardsDay::class, 'enrollment_id');
    }

    public function isRestDay(CarbonInterface $date): bool
    {
        return $date->dayOfWeek === $this->rest_weekday;
    }

    public function allGroupsComplete(): bool
    {
        return $this->unlocked_until >= Card::TOTAL;
    }

    /** @return array<int, int> groups that already hold at least one incorporated card */
    public function availableGroups(): array
    {
        $groups = [];
        for ($g = 1; $g <= Card::GROUPS; $g++) {
            if (Card::firstNumberOfGroup($g) <= $this->unlocked_until) {
                $groups[] = $g;
            }
        }

        return $groups;
    }

    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now($this->user->timezone ?? config('app.timezone'))->startOfDay();
    }

    /** Days elapsed since the start, for the six-month progress bar. */
    public function daysSinceStart(): int
    {
        return max(0, $this->started_at->diffInDays($this->today(), false));
    }
}
