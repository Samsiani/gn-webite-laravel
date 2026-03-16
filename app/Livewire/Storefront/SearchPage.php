<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Product;

class SearchPage extends Component
{
    use WithPagination;

    public string $query = '';

    public function mount(): void
    {
        $this->query = request('q', '');
    }

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = null;

        if (mb_strlen($this->query) >= 2) {
            if (config('scout.driver') === 'meilisearch') {
                $products = Product::search($this->query)
                    ->where('status', 'published')
                    ->query(fn ($query) => $query->with(['variants.prices', 'urls.language', 'media']))
                    ->paginate(24);
            } else {
                $search = $this->query;
                $table = (new Product)->getTable();
                $isMysql = config('database.default') === 'mysql';
                $jsonFn = $isMysql
                    ? "JSON_UNQUOTE(JSON_EXTRACT({$table}.attribute_data, '$.name.value.%s'))"
                    : "json_extract({$table}.attribute_data, '$.name.value.%s')";
                $products = Product::where('status', 'published')
                    ->where(function ($q) use ($search, $jsonFn) {
                        $q->whereRaw(sprintf($jsonFn, 'ka') . ' LIKE ?', ['%' . $search . '%'])
                          ->orWhereRaw(sprintf($jsonFn, 'en') . ' LIKE ?', ['%' . $search . '%'])
                          ->orWhereRaw(sprintf($jsonFn, 'ru') . ' LIKE ?', ['%' . $search . '%'])
                          ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'LIKE', '%' . $search . '%'));
                    })
                    ->with(['variants.prices', 'urls.language', 'media'])
                    ->paginate(24);
            }
        }

        $collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();
        $categories = $collectionGroup
            ? LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->whereIsRoot()->with(['urls.language'])->get()
            : collect();

        return view('livewire.storefront.search-page', [
            'products' => $products,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
