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

    public const STATUS_MINIMUM = 'minimum';

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
        return in_array($this->status, [self::STATUS_DONE, self::STATUS_MINIMUM], true);
    }
}
