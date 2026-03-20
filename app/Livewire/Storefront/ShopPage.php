<?php

namespace App\Livewire\Storefront;

use App\Filament\Pages\SiteSettings;
use App\Services\StorefrontData;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Product;

class ShopPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    public string $sort = 'latest';
    public ?int $categoryId = null;
    public ?float $priceMin = null;
    public ?float $priceMax = null;

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryId(): void
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

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->categoryId = null;
        $this->priceMin = null;
        $this->priceMax = null;
        $this->resetPage();
    }

    public function render()
    {
        $settings = SiteSettings::getSettings();
        $categoriesOnly = !empty($settings['shop_categories_only']);
        $hasFilters = $this->q || $this->categoryId || $this->priceMin || $this->priceMax;

        // Show category grid when setting is on and no filters/search active
        $showCategoryGrid = $categoriesOnly && !$hasFilters;

        // All root categories with product counts + media
        $categories = StorefrontData::categoriesWithCounts();

        if ($showCategoryGrid) {
            // Load media for category images
            $categories->load('media');

            // Empty paginator — no products needed
            $products = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        } else {
            $useMeilisearch = config('scout.driver') === 'meilisearch';
            $products = $useMeilisearch ? $this->searchWithMeilisearch() : $this->searchWithEloquent();
        }

        $shopTitle = $this->q
            ? __('Search') . ': "' . $this->q . '"'
            : __('Shop');

        $metaDesc = $showCategoryGrid
            ? __('Browse our product categories — professional kitchen equipment.')
            : __('Browse our catalog of professional kitchen equipment. :count products available.', ['count' => $products->total()]);

        return view('livewire.storefront.shop-page', [
            'products' => $products,
            'categories' => $categories,
            'hasFilters' => $hasFilters,
            'showCategoryGrid' => $showCategoryGrid,
        ])->layout('components.layouts.storefront', [
            'categories' => $categories,
            'metaTitle' => \App\Services\SeoHelper::title($shopTitle),
            'metaDescription' => $metaDesc,
            'canonical' => url(app()->getLocale() === 'ka' ? '/shop' : '/' . app()->getLocale() . '/shop'),
            'hreflangs' => ['ka' => url('/shop'), 'en' => url('/en/shop'), 'ru' => url('/ru/shop')],
        ]);
    }

    private function searchWithMeilisearch()
    {
        $builder = Product::search($this->q);

        // Status filter
        $builder->where('status', 'published');

        // Category filter — include descendants
        if ($this->categoryId) {
            $collection = LunarCollection::find($this->categoryId);
            if ($collection) {
                $collectionIds = $collection->descendants()->pluck('id')
                    ->merge([$collection->id])
                    ->map(fn ($id) => (int) $id)
                    ->toArray();
                $builder->whereIn('collection_ids', $collectionIds);
            }
        }

        // Price filter (stored in tetri)
        if ($this->priceMin) {
            $builder->where('price', '>=', (int) ($this->priceMin * 100));
        }
        if ($this->priceMax) {
            $builder->where('price', '<=', (int) ($this->priceMax * 100));
        }

        // Sorting
        $builder = match ($this->sort) {
            'price_asc' => $builder->orderBy('price', 'asc'),
            'price_desc' => $builder->orderBy('price', 'desc'),
            'name' => $builder->orderBy('name_' . app()->getLocale(), 'asc'),
            default => $builder->orderBy('created_at', 'desc'),
        };

        // Eager load relationships via Eloquent after Meilisearch returns IDs
        return $builder->query(fn ($query) => $query->with(['variants.prices', 'urls.language', 'media']))
            ->paginate(20);
    }

    private function searchWithEloquent()
    {
        $query = Product::where('status', 'published')
            ->with(['variants.prices', 'urls.language', 'media']);

        // Text search fallback
        if ($this->q) {
            $search = $this->q;
            $table = (new Product)->getTable();
            $jsonFn = "JSON_UNQUOTE(JSON_EXTRACT({$table}.attribute_data, '$.name.value.%s'))";
            $query->where(function ($q) use ($search, $jsonFn) {
                $q->whereRaw(sprintf($jsonFn, 'ka') . ' LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw(sprintf($jsonFn, 'en') . ' LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw(sprintf($jsonFn, 'ru') . ' LIKE ?', ['%' . $search . '%'])
                  ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'LIKE', '%' . $search . '%'));
            });
        }

        if ($this->categoryId) {
            $collection = LunarCollection::find($this->categoryId);
            if ($collection) {
                $collectionIds = $collection->descendants()->pluck('id')
                    ->merge([$collection->id]);
                $query->whereHas('collections', function ($q) use ($collectionIds) {
                    $q->whereIn('lunar_collections.id', $collectionIds);
                });
            }
        }

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

        $query = match ($this->sort) {
            'price_asc' => $query->orderByRaw('(SELECT MIN(price) FROM lunar_prices WHERE priceable_type = ? AND priceable_id IN (SELECT id FROM lunar_product_variants WHERE product_id = lunar_products.id)) ASC', ['product_variant']),
            'price_desc' => $query->orderByRaw('(SELECT MIN(price) FROM lunar_prices WHERE priceable_type = ? AND priceable_id IN (SELECT id FROM lunar_product_variants WHERE product_id = lunar_products.id)) DESC', ['product_variant']),
            'name' => $query->orderByRaw("json_extract(attribute_data, '$.name.value.ka') ASC"),
            default => $query->latest(),
        };

        return $query->paginate(20);
    }
}
