<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class TestResultRule extends Model
{
    use HasTranslations;

    protected $fillable = ['test_id', 'indicator', 'operator', 'value', 'title', 'content', 'position'];

    public array $translatable = ['title', 'content'];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function matches(array $indicators): bool
    {
        if (! array_key_exists($this->indicator, $indicators)) {
            return false;
        }

        $actual = $indicators[$this->indicator];
        $expected = is_numeric($this->value) ? (float) $this->value : $this->value;
        $actual = is_numeric($actual) ? (float) $actual : $actual;

        return match ($this->operator) {
            '=' => $actual == $expected,
            '!=' => $actual != $expected,
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
            default => false,
        };
    }
}
