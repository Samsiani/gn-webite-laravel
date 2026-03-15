<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-8">{{ __('Search') }}</h1>

        <div class="mb-8">
            <input type="search"
                   wire:model.live.debounce.300ms="query"
                   placeholder="{{ __('Search products...') }}"
                   class="w-full max-w-lg rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-lg px-6 py-3"
                   autofocus>
        </div>

        @if(strlen($query) >= 2)
            @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator ? $products->isNotEmpty() : $products->isNotEmpty())
                <p class="text-sm text-gray-500 mb-6">
                    {{ __('Results for') }} "<strong>{{ $query }}</strong>"
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        @include('livewire.storefront.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                @if($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
                @endif
            @else
                <p class="text-gray-500 text-center py-16">{{ __('No products found for') }} "{{ $query }}"</p>
            @endif
        @endif
    </div>
</div>
