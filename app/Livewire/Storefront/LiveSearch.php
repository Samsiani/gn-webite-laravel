<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Lunar\Models\Product;

class LiveSearch extends Component
{
    public string $query = '';
    public bool $showResults = false;

    public function updatedQuery(): void
    {
        $this->showResults = mb_strlen($this->query) >= 3;
    }

    public function clear(): void
    {
        $this->query = '';
        $this->showResults = false;
    }

    public function render()
    {
        $results = collect();

        if (mb_strlen($this->query) >= 3) {
            if (config('scout.driver') === 'meilisearch') {
                $results = Product::search($this->query)
                    ->where('status', 'published')
                    ->query(fn ($query) => $query->with(['variants.prices', 'urls.language', 'media']))
                    ->take(6)
                    ->get();
            } else {
                $search = $this->query;
                $table = (new Product)->getTable();
                $isMysql = config('database.default') === 'mysql';
                $jsonFn = $isMysql
                    ? "JSON_UNQUOTE(JSON_EXTRACT({$table}.attribute_data, '$.name.value.%s'))"
                    : "json_extract({$table}.attribute_data, '$.name.value.%s')";

                $results = Product::where('status', 'published')
                    ->where(function ($q) use ($search, $jsonFn) {
                        $q->whereRaw(sprintf($jsonFn, 'ka') . ' LIKE ?', ['%' . $search . '%'])
                          ->orWhereRaw(sprintf($jsonFn, 'en') . ' LIKE ?', ['%' . $search . '%'])
                          ->orWhereRaw(sprintf($jsonFn, 'ru') . ' LIKE ?', ['%' . $search . '%'])
                          ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'LIKE', '%' . $search . '%'));
                    })
                    ->with(['variants.prices', 'urls.language', 'media'])
                    ->limit(6)
                    ->get();
            }
        }

        return view('livewire.storefront.live-search', [
            'results' => $results,
        ]);
    }
}
