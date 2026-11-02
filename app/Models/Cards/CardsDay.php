<?php

namespace App\Models\Cards;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardsDay extends Model
{
    protected $fillable = [
        'enrollment_id', 'date', 'is_rest_day', 'group_index', 'reviewed_at', 'chosen_card_id', 'reviews_done', 'rating',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'immutable_date:Y-m-d',
            'is_rest_day' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CardsEnrollment::class, 'enrollment_id');
    }

    public function chosenCard(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'chosen_card_id');
    }
}
