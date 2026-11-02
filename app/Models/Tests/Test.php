<?php

namespace App\Models\Tests;

use App\Models\Contents\Content;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Test extends Model
{
    use HasTranslations;

    public const TYPE_JOHARI = 'johari';

    public const TYPE_SCORED = 'scored';

    protected $fillable = [
        'key', 'type', 'name', 'description', 'intro', 'general_content', 'config',
        'product_id', 'related_content_id', 'word_list_id', 'profiles_from_test_id', 'active', 'position', 'ai_enabled', 'ai_instructions',
    ];

    public array $translatable = ['name', 'description', 'intro', 'general_content', 'ai_instructions'];

    protected function casts(): array
    {
        return ['config' => 'array', 'active' => 'boolean', 'ai_enabled' => 'boolean'];
    }

    public function scales(): HasMany
    {
        return $this->hasMany(TestScale::class)->orderBy('position');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TestItem::class)->orderBy('position');
    }

    public function ownProfiles(): HasMany
    {
        return $this->hasMany(TestProfile::class);
    }

    /** Profiles may be shared with another test (e.g. two enneagram questionnaires). */
    public function profiles()
    {
        $source = $this->profiles_from_test_id ? static::find($this->profiles_from_test_id) : $this;

        return ($source ?? $this)->ownProfiles()->get()->keyBy('scale_key');
    }

    public function isScored(): bool
    {
        return $this->type === self::TYPE_SCORED;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function wordList(): BelongsTo
    {
        return $this->belongsTo(WordList::class);
    }

    public function relatedContent(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'related_content_id');
    }

    public function profilesSource(): BelongsTo
    {
        return $this->belongsTo(Test::class, 'profiles_from_test_id');
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
