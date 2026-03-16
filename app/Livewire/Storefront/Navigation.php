<?php

namespace App\Livewire\Storefront;

use App\Models\Menu;
use App\Models\Slide;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Navigation extends Component
{
    public bool $mobileOpen = false;

    public function toggleMobile(): void
    {
        $this->mobileOpen = ! $this->mobileOpen;
    }

    public function render()
    {
        $menu = Cache::remember('menu_main', 3600, function () {
            return Menu::with(['items' => fn ($q) => $q->where('is_active', true)->whereNull('parent_id')->orderBy('position')->with(['children' => fn ($q2) => $q2->where('is_active', true)->orderBy('position')])])
                ->where('handle', 'main')
                ->first();
        });

        return view('livewire.storefront.navigation', [
            'menuItems' => $menu?->items ?? collect(),
        ]);
    }
}
