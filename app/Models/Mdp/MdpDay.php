<?php

namespace App\Models\Mdp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MdpDay extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DONE = 'done';

    /** A reading counts as the full time from 15 minutes; shorter days are flagged in the notebook. */
    public const FULL_READING_SECONDS = 15 * 60;

    public const STATUS_REST = 'rest';

    protected $fillable = [
        'enrollment_id', 'date', 'status', 'started_at', 'finished_at', 'duration_seconds',
        'reading_from', 'reading_to', 'prayer', 'prayer_underline', 'visualized_at',
        'reviews_done', 'previous_reviewed',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date:Y-m-d',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'visualized_at' => 'datetime',
            'prayer' => 'encrypted',
            'prayer_underline' => 'encrypted',
            'previous_reviewed' => 'boolean',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(MdpEnrollment::class, 'enrollment_id');
    }

    public function texts(): HasMany
    {
        return $this->hasMany(MdpText::class, 'day_id')->orderBy('position');
    }

    public function dayText(): HasOne
    {
        return $this->hasOne(MdpText::class, 'day_id')->where('is_day_text', true);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    /** Done, but the timer did not reach the full reading time (or was never started). */
    public function isShortReading(): bool
    {
        return $this->isCompleted() && (int) $this->duration_seconds < self::FULL_READING_SECONDS;
    }

    public function durationMinutes(): int
    {
        return (int) round(((int) $this->duration_seconds) / 60);
    }
}
