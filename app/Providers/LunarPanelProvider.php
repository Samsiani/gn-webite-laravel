<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;

class LunarPanelProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::panel(function ($panel) {
            return $panel
                ->default()
                ->path('admin');
        })->register();
    }

    public function boot(): void
    {
        //
    }
}
