<?php

namespace App\Livewire\Storefront;

use App\Services\StorefrontData;
use Livewire\Component;
use Lunar\Models\Product;

class HomePage extends Component
{
    public function render()
    {
        $categories = StorefrontData::categories();

        $latest = Product::where('status', 'published')
            ->with(['variants.prices', 'urls.language', 'media'])
            ->limit(10)
            ->latest()
            ->get();

        $onSale = Product::where('status', 'published')
            ->whereHas('variants.prices', fn ($q) => $q->whereNotNull('compare_price')->where('compare_price', '>', 0))
            ->with(['variants.prices', 'urls.language', 'media'])
            ->limit(5)
            ->inRandomOrder()
            ->get();

        $popular = Product::where('status', 'published')
            ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
            ->with(['variants.prices', 'urls.language', 'media'])
            ->limit(5)
            ->inRandomOrder()
            ->get();

        $blogPosts = \App\Models\BlogPost::published()
            ->with('media')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('livewire.storefront.home-page', [
            'categories' => $categories,
            'latest' => $latest,
            'onSale' => $onSale,
            'popular' => $popular,
            'blogPosts' => $blogPosts,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
