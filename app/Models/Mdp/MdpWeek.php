<?php

namespace App\Models\Mdp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MdpWeek extends Model
{
    protected $fillable = [
        'enrollment_id', 'starts_on', 'ends_on', 'week_text_id', 'word_underlines',
        'rest_day_done_at', 'reviews_done', 'memorize_key', 'memorized',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'immutable_date:Y-m-d',
            'ends_on' => 'immutable_date:Y-m-d',
            'word_underlines' => 'array',
            'rest_day_done_at' => 'datetime',
            'memorized' => 'boolean',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(MdpEnrollment::class, 'enrollment_id');
    }

    public function weekText(): BelongsTo
    {
        return $this->belongsTo(MdpText::class, 'week_text_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(MdpDay::class, 'enrollment_id', 'enrollment_id')
            ->whereBetween('date', [$this->starts_on->toDateString(), $this->ends_on->toDateString()])
            ->orderBy('date');
    }

    /** The day texts chosen during this week (up to six). */
    public function dayTexts()
    {
        return MdpText::query()
            ->where('is_day_text', true)
            ->whereHas('day', fn ($q) => $q->where('enrollment_id', $this->enrollment_id)
                ->whereBetween('date', [$this->starts_on->toDateString(), $this->ends_on->toDateString()]))
            ->with('day')
            ->get()
            ->sortBy(fn (MdpText $t) => $t->day->date)
            ->values();
    }
}
