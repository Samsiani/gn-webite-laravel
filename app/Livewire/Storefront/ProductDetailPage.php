<?php

namespace App\Livewire\Storefront;

use App\Services\StorefrontData;
use Livewire\Component;
use Lunar\Models\Product;
use Lunar\Models\Url;

class ProductDetailPage extends Component
{
    public Product $product;
    public int $quantity = 1;
    public int $activeImage = 0;

    public function mount(string $slug)
    {
        $locale = app()->getLocale();
        $languageId = \Lunar\Models\Language::where('code', $locale)->value('id');

        $url = Url::where('slug', $slug)
            ->where('element_type', 'product')
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->first();

        if (! $url) {
            $url = Url::where('slug', $slug)
                ->where('element_type', 'product')
                ->first();
        }

        if (! $url) {
            abort(404);
        }

        $this->product = Product::with(['variants.prices', 'collections.urls.language', 'media', 'urls.language'])
            ->findOrFail($url->element_id);
    }

    public function setImage(int $index): void
    {
        $this->activeImage = $index;
    }

    public function render()
    {
        $locale = app()->getLocale();
        $prefix = $locale === 'ka' ? '' : '/' . $locale;

        $variant = $this->product->variants->first();
        $priceObj = $variant?->prices->first();
        $price = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
        $comparePrice = ($priceObj?->compare_price && $priceObj->compare_price->value > 0)
            ? number_format($priceObj->compare_price->value / 100, 2)
            : null;
        $onSale = $comparePrice && $comparePrice > $price;

        // Hreflang URLs
        $hreflangs = [];
        $urls = $this->product->urls;
        foreach ($urls as $url) {
            $lang = $url->language?->code;
            if (! $lang) continue;
            $langPrefix = $lang === 'ka' ? '' : "/{$lang}";
            $hreflangs[$lang] = url("{$langPrefix}/product/{$url->slug}");
        }

        // Specs from product_specs table with translations
        $specs = [];
        $productSpecs = \App\Models\ProductSpec::where('product_id', $this->product->id)
            ->orderBy('position')
            ->with('attribute')
            ->get();

        foreach ($productSpecs as $ps) {
            // Translated attribute name
            $label = $ps->attribute?->name ?? '';
            if ($locale === 'en' && $ps->attribute?->name_en) {
                $label = $ps->attribute->name_en;
            } elseif ($locale === 'ru' && $ps->attribute?->name_ru) {
                $label = $ps->attribute->name_ru;
            }

            // Translated value
            $value = $ps->value;
            if ($locale === 'en' && $ps->value_en) {
                $value = $ps->value_en;
            } elseif ($locale === 'ru' && $ps->value_ru) {
                $value = $ps->value_ru;
            }

            if ($label && $value) {
                $specs[$label] = $value;
            }
        }

        // Breadcrumb
        $collection = $this->product->collections->first();
        $breadcrumbs = collect();
        if ($collection) {
            $ancestors = $collection->ancestors()->with(['urls.language'])->get();
            $breadcrumbs = $ancestors->push($collection);
        }

        // Related products
        $related = collect();
        if ($collection) {
            $related = Product::where('status', 'published')
                ->where('id', '!=', $this->product->id)
                ->whereHas('collections', fn ($q) => $q->where('lunar_collections.id', $collection->id))
                ->with(['variants.prices', 'urls.language', 'media'])
                ->limit(5)
                ->inRandomOrder()
                ->get();
        }

        // Root categories for nav
        $categories = StorefrontData::categories();

        // Merge featured image + gallery images
        $images = $this->product->getMedia('images')
            ->merge($this->product->getMedia('gallery'));

        $shortDescription = $this->product->translateAttribute('short_description', $locale)
            ?? $this->product->translateAttribute('short_description');

        // SEO meta
        $name = $this->product->translateAttribute('name', $locale) ?? $this->product->translateAttribute('name');
        $desc = $this->product->translateAttribute('description', $locale) ?? $this->product->translateAttribute('description');
        $metaDesc = \Illuminate\Support\Str::limit(strip_tags($shortDescription ?: $desc), 160);
        $ogImg = $this->product->getFirstMediaUrl('images', 'large') ?: $this->product->getFirstMediaUrl('images');

        return view('livewire.storefront.product-detail-page', [
            'price' => $price,
            'comparePrice' => $comparePrice,
            'onSale' => $onSale,
            'hreflangs' => $hreflangs,
            'locale' => $locale,
            'prefix' => $prefix,
            'specs' => $specs,
            'shortDescription' => $shortDescription,
            'breadcrumbs' => $breadcrumbs,
            'collection' => $collection,
            'related' => $related,
            'images' => $images,
            'variant' => $variant,
        ])->layout('components.layouts.storefront', [
            'categories' => $categories,
            'metaTitle' => \App\Services\SeoHelper::title($name),
            'metaDescription' => $metaDesc ?: \App\Services\SeoHelper::defaultDescription(),
            'canonical' => url()->current(),
            'hreflangs' => $hreflangs,
            'ogType' => 'product',
            'ogImage' => $ogImg ?: null,
        ]);
    }
}
