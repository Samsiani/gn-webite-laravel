<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BlogPost extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'blog_category_id', 'title', 'title_en', 'title_ru',
        'slug', 'slug_en', 'slug_ru',
        'excerpt', 'excerpt_en', 'excerpt_ru',
        'content', 'content_en', 'content_ru',
        'blocks', 'blocks_en', 'blocks_ru',
        'meta_title', 'meta_title_en', 'meta_title_ru',
        'meta_description', 'meta_description_en', 'meta_description_ru',
        'status', 'published_at', 'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'blocks' => 'array',
        'blocks_en' => 'array',
        'blocks_ru' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(BlogPostTag::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }

    // Translation helpers
    public function t(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translated = match ($locale) {
            'en' => $this->{$field . '_en'},
            'ru' => $this->{$field . '_ru'},
            default => $this->{$field},
        };
        return $translated ?: $this->{$field};
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }
}
