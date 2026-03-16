<?php

namespace App\Providers;

use App\Filament\Extensions\ProductResourceExtension;
use App\Filament\Pages\MediaLibrary;
use App\Filament\Pages\SiteSettings;
use App\Filament\Pages\TranslationManager;
use App\Filament\Resources\BlogPostResource;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\SlideResource;
use App\Filament\Resources\SpecAttributeResource;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Facades\LunarPanel;

class LunarPanelProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::extensions([
            ProductResource::class => ProductResourceExtension::class,
        ])->panel(function ($panel) {
            return $panel
                ->default()
                ->path('admin')
                ->resources([
                    SpecAttributeResource::class,
                    BlogPostResource::class,
                    MenuResource::class,
                    SlideResource::class,
                ])
                ->pages([
                    MediaLibrary::class,
                    SiteSettings::class,
                    TranslationManager::class,
                ]);
        })->register();
    }

    public function boot(): void
    {
        //
    }
}
