<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id', 'parent_id', 'label', 'label_en', 'label_ru',
        'url', 'type', 'reference_id', 'open_new_tab', 'icon', 'position', 'is_active',
    ];

    protected $casts = [
        'open_new_tab' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('is_active', true)->orderBy('position');
    }

    public function getTranslatedLabel(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        return match ($locale) {
            'en' => $this->label_en ?: $this->label,
            'ru' => $this->label_ru ?: $this->label,
            default => $this->label,
        };
    }

    public function getResolvedUrl(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $prefix = $locale === 'ka' ? '' : '/' . $locale;

        if ($this->type === 'custom' && $this->url) {
            // Prepend locale prefix if it's a relative URL
            if (str_starts_with($this->url, '/') && ! str_starts_with($this->url, '/en') && ! str_starts_with($this->url, '/ru')) {
                return $prefix . $this->url;
            }
            return $this->url;
        }

        if ($this->type === 'category' && $this->reference_id) {
            $collection = \Lunar\Models\Collection::find($this->reference_id);
            if ($collection) {
                $url = $collection->urls->first(fn ($u) => $u->language?->code === $locale)
                    ?? $collection->urls->firstWhere('default', true);
                return $prefix . '/category/' . ($url?->slug ?? '');
            }
        }

        return $prefix . ($this->url ?? '/');
    }
}
