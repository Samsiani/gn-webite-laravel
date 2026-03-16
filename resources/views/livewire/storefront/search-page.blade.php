@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('Search') }}</span>
        </nav>

        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Search Products') }}</h1>

        {{-- Search Input --}}
        <div class="mb-8 max-w-xl">
            <div class="relative">
                <input type="search"
                       wire:model.live.debounce.300ms="query"
                       placeholder="{{ __('Type to search...') }}"
                       class="w-full rounded-xl border border-gray-200 bg-white pl-12 pr-4 py-3.5 text-base focus:border-primary focus:ring-1 focus:ring-primary transition"
                       autofocus>
                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        @if(strlen($query) >= 2 && $products !== null)
            @if($products->isNotEmpty())
                <p class="text-sm text-gray-500 mb-5">
                    {{ $products->total() }} {{ __('results for') }} "<strong class="text-gray-900">{{ $query }}</strong>"
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
                    @foreach($products as $product)
                        @include('livewire.storefront.partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-20">
                    <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <p class="text-gray-400 text-lg">{{ __('No products found for') }} "{{ $query }}"</p>
                    <p class="text-gray-300 text-sm mt-2">{{ __('Try a different search term') }}</p>
                </div>
            @endif
        @elseif(strlen($query) > 0 && strlen($query) < 2)
            <p class="text-gray-400 text-sm">{{ __('Type at least 2 characters to search') }}</p>
        @endif
    </div>
</div>
