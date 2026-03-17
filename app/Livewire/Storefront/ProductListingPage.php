<?php

namespace App\Livewire\Storefront;

use App\Services\StorefrontData;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Product;
use Lunar\Models\Url;

class ProductListingPage extends Component
{
    use WithPagination;

    public ?LunarCollection $collection = null;
    public string $sort = 'latest';
    public ?float $priceMin = null;
    public ?float $priceMax = null;

    public function mount(string $slug)
    {
        $locale = app()->getLocale();
        $languageId = \Lunar\Models\Language::where('code', $locale)->value('id');

        // Try current locale first, then any language
        $url = Url::where('slug', $slug)
            ->where('element_type', 'collection')
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->first();

        if (! $url) {
            $url = Url::where('slug', $slug)
                ->where('element_type', 'collection')
                ->first();
        }

        if (! $url) {
            abort(404);
        }

        $this->collection = LunarCollection::with(['urls.language'])->find($url->element_id);
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedPriceMin(): void
    {
        $this->resetPage();
    }

    public function updatedPriceMax(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $locale = app()->getLocale();

        $query = Product::where('status', 'published')
            ->with(['variants.prices', 'urls.language', 'media']);

        if ($this->collection) {
            $collectionIds = $this->collection->descendants()->pluck('id')
                ->merge([$this->collection->id]);

            $query->whereHas('collections', function ($q) use ($collectionIds) {
                $q->whereIn('lunar_collections.id', $collectionIds);
            });
        }

        // Price filter
        if ($this->priceMin || $this->priceMax) {
            $query->whereHas('variants.prices', function ($q) {
                if ($this->priceMin) {
                    $q->where('price', '>=', (int) ($this->priceMin * 100));
                }
                if ($this->priceMax) {
                    $q->where('price', '<=', (int) ($this->priceMax * 100));
                }
            });
        }

        // Sorting
        $query = match ($this->sort) {
            'price_asc' => $query->orderByRaw('(SELECT MIN(price) FROM lunar_prices WHERE priceable_type = ? AND priceable_id IN (SELECT id FROM lunar_product_variants WHERE product_id = lunar_products.id)) ASC', ['product_variant']),
            'price_desc' => $query->orderByRaw('(SELECT MIN(price) FROM lunar_prices WHERE priceable_type = ? AND priceable_id IN (SELECT id FROM lunar_product_variants WHERE product_id = lunar_products.id)) DESC', ['product_variant']),
            'name' => $query->orderByRaw("json_extract(attribute_data, '$.name.value.ka') ASC"),
            default => $query->latest(),
        };

        $products = $query->paginate(20);

        // Subcategories
        $children = $this->collection
            ? LunarCollection::where('parent_id', $this->collection->id)
                ->with(['urls.language'])
                ->get()
            : collect();

        // Root categories for nav
        $categories = StorefrontData::categories();

        // Breadcrumbs
        $breadcrumbs = collect();
        if ($this->collection) {
            $ancestors = $this->collection->ancestors()->with(['urls.language'])->get();
            $breadcrumbs = $ancestors->push($this->collection);
        }

        // SEO meta
        $locale = app()->getLocale();
        $catName = $this->collection?->translateAttribute('name', $locale) ?? $this->collection?->translateAttribute('name') ?? __('Category');
        $catDesc = $this->collection?->translateAttribute('description', $locale) ?? '';
        $hreflangs = [];
        if ($this->collection) {
            foreach ($this->collection->urls as $url) {
                $lang = $url->language?->code;
                if (! $lang) continue;
                $langPrefix = $lang === 'ka' ? '' : "/{$lang}";
                $hreflangs[$lang] = url("{$langPrefix}/category/{$url->slug}");
            }
        }

        return view('livewire.storefront.product-listing-page', [
            'products' => $products,
            'children' => $children,
            'categories' => $categories,
            'breadcrumbs' => $breadcrumbs,
        ])->layout('components.layouts.storefront', [
            'categories' => $categories,
            'metaTitle' => \App\Services\SeoHelper::title($catName),
            'metaDescription' => \Illuminate\Support\Str::limit(strip_tags($catDesc), 160) ?: __(':category — professional kitchen equipment', ['category' => $catName]),
            'canonical' => url()->current(),
            'hreflangs' => $hreflangs,
        ]);
    }
}
