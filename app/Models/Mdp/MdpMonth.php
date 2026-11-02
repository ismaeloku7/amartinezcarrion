<?php

namespace App\Models\Mdp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdpMonth extends Model
{
    protected $fillable = ['enrollment_id', 'index', 'starts_on', 'ends_on', 'month_text_id', 'closed_at'];

    protected function casts(): array
    {
        return [
            'starts_on' => 'immutable_date:Y-m-d',
            'ends_on' => 'immutable_date:Y-m-d',
            'closed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(MdpEnrollment::class, 'enrollment_id');
    }

    public function monthText(): BelongsTo
    {
        return $this->belongsTo(MdpText::class, 'month_text_id');
    }

    public function weeks()
    {
        return MdpWeek::query()
            ->where('enrollment_id', $this->enrollment_id)
            ->whereBetween('starts_on', [$this->starts_on->toDateString(), $this->ends_on->toDateString()])
            ->with('weekText')
            ->orderBy('starts_on')
            ->get();
    }
}
