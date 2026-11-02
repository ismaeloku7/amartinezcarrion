<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class TestScale extends Model
{
    use HasTranslations;

    protected $fillable = ['test_id', 'key', 'name', 'color', 'group', 'position'];

    public array $translatable = ['name'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
