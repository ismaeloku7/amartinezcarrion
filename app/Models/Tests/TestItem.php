<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class TestItem extends Model
{
    use HasTranslations;

    protected $fillable = ['test_id', 'position', 'group_key', 'statement', 'weights'];

    public array $translatable = ['statement'];

    protected function casts(): array
    {
        return ['weights' => 'array'];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
