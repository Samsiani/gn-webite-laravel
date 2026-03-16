<?php

namespace App\Providers;

use App\Listeners\ConvertMediaToWebp;
use App\Shipping\FreeShipping;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Lunar\Base\ShippingModifiers;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        app(ShippingModifiers::class)->add(FreeShipping::class);

        Event::listen(MediaHasBeenAddedEvent::class, ConvertMediaToWebp::class);
    }
}
