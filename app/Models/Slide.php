<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slide extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'title', 'title_en', 'title_ru',
        'subtitle', 'subtitle_en', 'subtitle_ru',
        'badge', 'badge_en', 'badge_ru',
        'cta_text', 'cta_text_en', 'cta_text_ru', 'cta_url',
        'cta2_text', 'cta2_text_en', 'cta2_text_ru', 'cta2_url',
        'bg_type', 'bg_gradient', 'overlay_color',
        'show_stats', 'stats', 'is_active', 'position',
    ];

    protected $casts = [
        'show_stats' => 'boolean',
        'is_active' => 'boolean',
        'stats' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('background')->singleFile();
    }

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

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('position');
    }
}
