<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Lunar\Models\Product;
use Lunar\Models\Url;

class ProductDetailPage extends Component
{
    public Product $product;
    public int $quantity = 1;

    public function mount(string $slug)
    {
        $locale = app()->getLocale();
        $languageId = \Lunar\Models\Language::where('code', $locale)->value('id');

        $url = Url::where('slug', $slug)
            ->where('element_type', Product::class)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->first();

        if (! $url) {
            abort(404);
        }

        $this->product = Product::with(['variants.prices', 'collections'])
            ->findOrFail($url->element_id);
    }

    public function getPrice()
    {
        $variant = $this->product->variants->first();
        $price = $variant?->prices->first();

        if (! $price) {
            return null;
        }

        return number_format($price->price->value / 100, 2);
    }

    public function render()
    {
        $locale = app()->getLocale();

        // Get hreflang URLs
        $hreflangs = [];
        $urls = Url::where('element_type', Product::class)
            ->where('element_id', $this->product->id)
            ->with('language')
            ->get();

        foreach ($urls as $url) {
            $lang = $url->language->code;
            $prefix = $lang === 'ka' ? '' : "/{$lang}";
            $hreflangs[$lang] = url("{$prefix}/product/{$url->slug}");
        }

        return view('livewire.storefront.product-detail-page', [
            'price' => $this->getPrice(),
            'hreflangs' => $hreflangs,
            'locale' => $locale,
        ])->layout('components.layouts.storefront');
    }
}
