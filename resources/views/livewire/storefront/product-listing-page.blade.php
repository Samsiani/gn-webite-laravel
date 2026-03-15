<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        @if($collection)
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary">{{ __('Home') }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $collection->translateAttribute('name', app()->getLocale()) ?? $collection->translateAttribute('name') }}</span>
        </nav>

        <h1 class="text-3xl font-bold mb-8">
            {{ $collection->translateAttribute('name', app()->getLocale()) ?? $collection->translateAttribute('name') }}
        </h1>
        @endif

        {{-- Products Grid --}}
        @if($products->isNotEmpty())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('livewire.storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
        @else
        <p class="text-gray-500 text-center py-16">{{ __('No products found.') }}</p>
        @endif
    </div>
</div>
