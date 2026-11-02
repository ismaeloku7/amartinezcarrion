<?php

namespace App\Models\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class WordList extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'name', 'description', 'active', 'is_default'];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'is_default' => 'boolean'];
    }

    public function words(): HasMany
    {
        return $this->hasMany(Word::class)->orderBy('position');
    }

    public function duplicate(string $newKey, string $newName): self
    {
        $copy = $this->replicate(['key', 'is_default']);
        $copy->key = $newKey;
        $copy->setTranslation('name', app()->getLocale(), $newName);
        $copy->is_default = false;
        $copy->save();

        foreach ($this->words as $word) {
            $w = $word->replicate(['word_list_id']);
            $w->word_list_id = $copy->id;
            $w->save();
        }

        return $copy;
    }
}
