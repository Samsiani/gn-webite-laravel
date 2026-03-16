<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    protected $fillable = ['name', 'name_en', 'name_ru', 'slug', 'slug_en', 'slug_ru', 'position'];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function getTranslatedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return match ($locale) {
            'en' => $this->name_en ?: $this->name,
            'ru' => $this->name_ru ?: $this->name,
            default => $this->name,
        };
    }

    public function getTranslatedSlug(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return match ($locale) {
            'en' => $this->slug_en ?: $this->slug,
            'ru' => $this->slug_ru ?: $this->slug,
            default => $this->slug,
        };
    }
}
