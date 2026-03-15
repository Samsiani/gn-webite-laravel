<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Livewire\WithPagination;
use Lunar\Models\Product;

class SearchPage extends Component
{
    use WithPagination;

    public string $query = '';

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = collect();

        if (strlen($this->query) >= 2) {
            if (config('scout.driver') === 'meilisearch') {
                $products = Product::search($this->query)->paginate(24);
            } else {
                // Fallback: basic LIKE search on attribute_data
                $products = Product::where('status', 'published')
                    ->where('attribute_data', 'LIKE', '%' . $this->query . '%')
                    ->paginate(24);
            }
        }

        return view('livewire.storefront.search-page', [
            'products' => $products,
        ])->layout('components.layouts.storefront');
    }
}
