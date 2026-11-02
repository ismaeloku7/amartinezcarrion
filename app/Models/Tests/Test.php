<?php

namespace App\Models\Tests;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Test extends Model
{
    use HasTranslations;

    public const TYPE_JOHARI = 'johari';

    protected $fillable = [
        'key', 'type', 'name', 'description', 'intro', 'general_content', 'config',
        'product_id', 'word_list_id', 'active', 'position',
    ];

    public array $translatable = ['name', 'description', 'intro', 'general_content'];

    protected function casts(): array
    {
        return ['config' => 'array', 'active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function wordList(): BelongsTo
    {
        return $this->belongsTo(WordList::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(TestResultRule::class)->orderBy('position');
    }

    public function takes(): HasMany
    {
        return $this->hasMany(TestTake::class);
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    public function requiresAccess(): bool
    {
        return $this->product_id !== null;
    }
}
