<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;

class ShopPage extends Component
{
    use WithPagination;

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

    public function clearFilters(): void
    {
        $this->categoryId = null;
        $this->priceMin = null;
        $this->priceMax = null;
        $this->resetPage();
    }

    public function render()
    {
        $useMeilisearch = config('scout.driver') === 'meilisearch';

        if ($useMeilisearch) {
            $products = $this->searchWithMeilisearch();
        } else {
            $products = $this->searchWithEloquent();
        }

        // All root categories with product counts
        $collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();
        $categories = $collectionGroup
            ? LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->whereIsRoot()
                ->with(['urls.language'])
                ->withCount('products')
                ->get()
            : collect();

        $hasFilters = $this->categoryId || $this->priceMin || $this->priceMax;

        return view('livewire.storefront.shop-page', [
            'products' => $products,
            'categories' => $categories,
            'hasFilters' => $hasFilters,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }

    private function searchWithMeilisearch()
    {
        $builder = Product::search('');

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
