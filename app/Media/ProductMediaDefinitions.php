<?php

namespace App\Media;

use Lunar\Base\StandardMediaDefinitions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductMediaDefinitions extends StandardMediaDefinitions
{
    public function registerMediaConversions(HasMedia $model, ?Media $media = null): void
    {
        // Thumb: 150x150 WebP — cart, mini cart, live search, mega menu
        $model->addMediaConversion('thumb')
            ->fit(Fit::Contain, 150, 150)
            ->background('#FFF')
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        // Small: 300x300 WebP — admin panel
        $model->addMediaConversion('small')
            ->fit(Fit::Contain, 300, 300)
            ->background('#FFF')
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        // Medium: 400x400 WebP — product grid cards, homepage
        $model->addMediaConversion('medium')
            ->fit(Fit::Contain, 400, 400)
            ->background('#FFF')
            ->format('webp')
            ->quality(85)
            ->nonQueued();

        // Large: 800x800 WebP — single product page
        $model->addMediaConversion('large')
            ->fit(Fit::Contain, 800, 800)
            ->background('#FFF')
            ->format('webp')
            ->quality(90)
            ->nonQueued();
    }
}
