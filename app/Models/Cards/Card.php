<?php

namespace App\Models\Cards;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Card extends Model
{
    use HasTranslations;

    public const TOTAL = 42;

    public const GROUP_SIZE = 7;

    public const GROUPS = 6;

    /** Group colours as printed on the cards. */
    public const GROUP_COLORS = [
        1 => '#b5121b', 2 => '#2b5d8c', 3 => '#3c8c5b', 4 => '#4fb3a6', 5 => '#e07a3a', 6 => '#8a6a5a',
    ];

    protected $fillable = ['number', 'group_index', 'title', 'front_text', 'back_text', 'section_summary', 'image_path', 'selectable'];

    public array $translatable = ['title', 'front_text', 'back_text', 'section_summary'];

    protected function casts(): array
    {
        return ['selectable' => 'boolean'];
    }

    public static function groupOf(int $number): int
    {
        return intdiv($number - 1, self::GROUP_SIZE) + 1;
    }

    public static function firstNumberOfGroup(int $group): int
    {
        return ($group - 1) * self::GROUP_SIZE + 1;
    }

    public function color(): string
    {
        return self::GROUP_COLORS[$this->group_index] ?? '#444';
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset($this->image_path) : null;
    }
}
