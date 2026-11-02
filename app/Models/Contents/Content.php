<?php

namespace App\Models\Contents;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Content extends Model
{
    use HasTranslations;

    public const TYPE_ARTICLE = 'article';

    public const TYPE_COURSE = 'course';

    protected $fillable = [
        'topic_id', 'slug', 'type', 'title', 'excerpt', 'body', 'cover_color', 'author', 'reading_minutes',
        'product_id', 'requires_login', 'published_at', 'position',
    ];

    public array $translatable = ['title', 'excerpt', 'body'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'requires_login' => 'boolean'];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function color(): string
    {
        return $this->cover_color ?: ($this->topic?->color ?: '#1f3a5f');
    }

    public function readingMinutes(): int
    {
        if ($this->reading_minutes) {
            return $this->reading_minutes;
        }
        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }
}
