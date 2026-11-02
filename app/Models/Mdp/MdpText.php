<?php

namespace App\Models\Mdp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MdpText extends Model
{
    public const MAX_WORDS = 12;

    public const WARN_WORDS = 10;

    protected $fillable = ['day_id', 'position', 'body', 'underline', 'is_day_text', 'word_count', 'note_type', 'note'];

    protected function casts(): array
    {
        return ['underline' => 'array', 'is_day_text' => 'boolean'];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(MdpDay::class, 'day_id');
    }

    public static function countWords(string $body): int
    {
        return count(preg_split('/\s+/u', trim($body), -1, PREG_SPLIT_NO_EMPTY));
    }

    /** @return array<int, string> */
    public function words(): array
    {
        return preg_split('/\s+/u', trim($this->body), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
