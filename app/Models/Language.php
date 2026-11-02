<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    protected $fillable = ['code', 'name', 'active', 'is_default', 'position'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'is_default' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('languages.active'));
        static::deleted(fn () => Cache::forget('languages.active'));
    }

    /** @return array<int, string> */
    public static function activeCodes(): array
    {
        return Cache::rememberForever('languages.active', function () {
            $codes = static::query()->where('active', true)->orderBy('position')->pluck('code')->all();

            return $codes ?: [config('app.locale')];
        });
    }

    public static function defaultCode(): string
    {
        return static::query()->where('is_default', true)->value('code') ?? config('app.locale');
    }
}
