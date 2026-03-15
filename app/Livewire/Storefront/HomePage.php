<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;

class HomePage extends Component
{
    public function render()
    {
        $collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();

        $categories = $collectionGroup
            ? LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->whereIsRoot()
                ->get()
            : collect();

        $featured = Product::where('status', 'published')
            ->limit(8)
            ->latest()
            ->get();

        return view('livewire.storefront.home-page', [
            'categories' => $categories,
            'featured' => $featured,
        ])->layout('components.layouts.storefront');
    }
}
