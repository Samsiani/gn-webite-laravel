<?php

namespace App\Media;

use Lunar\Base\StandardMediaDefinitions;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CollectionMediaDefinitions extends StandardMediaDefinitions
{
    public function registerMediaConversions(HasMedia $model, ?Media $media = null): void
    {
        // Thumb: 150x150 WebP — homepage cards, mega menu, navigation
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
    }
}
