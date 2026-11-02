<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Word extends Model
{
    use HasTranslations;

    protected $fillable = ['word_list_id', 'key', 'label', 'hint', 'tone', 'position'];

    public array $translatable = ['label', 'hint'];

    public function wordList(): BelongsTo
    {
        return $this->belongsTo(WordList::class);
    }
}
