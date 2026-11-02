<?php

namespace App\Models\Contents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Topic extends Model
{
    use HasTranslations;

    protected $fillable = ['parent_id', 'slug', 'name', 'description', 'color', 'position'];

    public array $translatable = ['name', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Topic::class, 'parent_id')->orderBy('position');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class)->orderBy('position');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Contents of this topic and of its subtopics. */
    public function allContents()
    {
        $ids = $this->children()->pluck('id')->push($this->id);

        return Content::query()->whereIn('topic_id', $ids)->published()->listed()->orderBy('position')->get();
    }
}
