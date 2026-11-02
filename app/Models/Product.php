<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    public const TYPE_PRACTICE = 'practice';

    public const TYPE_TEST = 'test';

    public const TYPE_CONTENT = 'content';

    public const TYPE_COURSE = 'course';

    protected $fillable = ['key', 'type', 'name', 'description', 'is_free', 'active'];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['is_free' => 'boolean', 'active' => 'boolean'];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }
}
