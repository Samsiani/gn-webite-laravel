<?php

namespace App\Livewire\Storefront;

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
    public string $search = '';

    public function mount(string $slug)
    {
        $locale = app()->getLocale();
        $languageId = \Lunar\Models\Language::where('code', $locale)->value('id');

        $url = Url::where('slug', $slug)
            ->where('element_type', LunarCollection::class)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->first();

        if (! $url) {
            abort(404);
        }

        $this->collection = LunarCollection::find($url->element_id);
    }

    public function render()
    {
        $query = Product::where('status', 'published');

        if ($this->collection) {
            $collectionIds = $this->collection->descendants()->pluck('id')
                ->merge([$this->collection->id]);

            $query->whereHas('collections', function ($q) use ($collectionIds) {
                $q->whereIn('lunar_collections.id', $collectionIds);
            });
        }

        $products = $query->paginate(24);

        return view('livewire.storefront.product-listing-page', [
            'products' => $products,
        ])->layout('components.layouts.storefront');
    }
}
