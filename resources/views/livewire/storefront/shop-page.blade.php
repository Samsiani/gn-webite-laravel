@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('Shop') }}</span>
        </nav>

        @if($showCategoryGrid)
            {{-- ═══ Category Grid Mode ═══ --}}
            <div class="mb-6">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('Shop') }}</h1>
                <p class="text-sm text-gray-400 mt-1">{{ __('Browse by category') }}</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($categories as $cat)
                    @php
                        $catName = $cat->translateAttribute('name', $locale) ?? $cat->translateAttribute('name');
                        $catUrl = $cat->urls->first(fn($u) => $u->language?->code === $locale) ?? $cat->urls->first();
                        $catSlug = $catUrl?->slug ?? '';
                        $catImage = $cat->getFirstMediaUrl('images', 'small') ?: $cat->getFirstMediaUrl('images');
                        if (!$catImage) {
                            // Fallback: first product image from this category
                            $firstProduct = $cat->products()->with('media')->first();
                            $catImage = $firstProduct?->getFirstMediaUrl('images', 'small') ?: $firstProduct?->getFirstMediaUrl('images');
                        }
                        $productCount = $cat->products_count ?? 0;
                    @endphp
                    <a wire:navigate href="{{ $prefix }}/category/{{ $catSlug }}"
                       class="group bg-white rounded-xl border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-lg hover:-translate-y-1 cursor-pointer">
                        <div class="aspect-square bg-white overflow-hidden flex items-center justify-center p-4">
                            @if($catImage)
                                <img src="{{ $catImage }}" alt="{{ $catName }}" loading="lazy"
                                     onload="this.classList.add('loaded')"
                                     class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
                            @else
                                <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            @endif
                        </div>
                        <div class="p-4 border-t border-gray-50 text-center">
                            <h3 class="font-semibold text-gray-800 group-hover:text-primary transition text-sm leading-snug">{{ $catName }}</h3>
                            @if($productCount > 0)
                                <p class="text-xs text-gray-400 mt-1">{{ $productCount }} {{ __('products') }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            {{-- ═══ Default Product Listing Mode ═══ --}}
            <div class="flex gap-6">
                {{-- Sidebar Filters --}}
                <aside class="hidden lg:block shrink-0 space-y-4" style="width:300px">

                    {{-- Categories Filter --}}
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-semibold text-sm text-gray-900 mb-3">{{ __('Categories') }}</h3>
                        <ul class="space-y-0.5">
                            <li>
                                <button wire:click="$set('categoryId', null)"
                                        class="w-full text-left py-1.5 px-2 rounded-lg text-sm transition
                                               {{ !$categoryId ? 'bg-primary-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                    {{ __('All Products') }}
                                </button>
                            </li>
                            @foreach($categories as $cat)
                                @php
                                    $catName = $cat->translateAttribute('name', $locale) ?? $cat->translateAttribute('name');
                                @endphp
                                <li>
                                    <button wire:click="$set('categoryId', {{ $cat->id }})"
                                            class="w-full text-left py-1.5 px-2 rounded-lg text-sm transition
                                                   {{ $categoryId == $cat->id ? 'bg-primary-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                        {{ $catName }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Price Filter --}}
                    <div class="bg-white rounded-xl border border-gray-100 p-4">
                        <h3 class="font-semibold text-sm text-gray-900 mb-3">{{ __('Price') }} (₾)</h3>
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <input type="number" wire:model.live.debounce.500ms="priceMin"
                                       placeholder="{{ __('Min') }}"
                                       min="0" step="1"
                                       class="w-full rounded-lg border-gray-200 text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            </div>
                            <span class="text-gray-300">—</span>
                            <div class="flex-1">
                                <input type="number" wire:model.live.debounce.500ms="priceMax"
                                       placeholder="{{ __('Max') }}"
                                       min="0" step="1"
                                       class="w-full rounded-lg border-gray-200 text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            </div>
                        </div>
                    </div>
                </aside>

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900">
                                @if($q)
                                    {{ __('Search') }}: "{{ $q }}"
                                @else
                                    {{ __('Shop') }}
                                @endif
                            </h1>
                            <p class="text-sm text-gray-400 mt-1">{{ $products->total() }} {{ __('products') }}</p>
                        </div>
                        <select wire:model.live="sort"
                                class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="latest">{{ __('Latest') }}</option>
                            <option value="price_asc">{{ __('Price: Low to High') }}</option>
                            <option value="price_desc">{{ __('Price: High to Low') }}</option>
                            <option value="name">{{ __('Name') }}</option>
                        </select>
                    </div>

                    {{-- Mobile Filters Toggle --}}
                    <div class="lg:hidden mb-4 flex gap-2">
                        <button onclick="document.getElementById('mobile-filters').classList.toggle('hidden')"
                                class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:border-primary hover:text-primary transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            {{ __('Filters') }}
                        </button>
                        @if($hasFilters)
                            <button wire:click="clearFilters" class="px-4 py-2 text-sm text-primary hover:underline">{{ __('Clear filters') }}</button>
                        @endif
                    </div>

                    {{-- Mobile Filters --}}
                    <div id="mobile-filters" class="hidden lg:hidden mb-4 bg-white rounded-xl border border-gray-100 p-4 space-y-4">
                        {{-- Mobile Categories --}}
                        <div>
                            <h3 class="font-semibold text-sm text-gray-900 mb-2">{{ __('Categories') }}</h3>
                            <div class="flex flex-wrap gap-1.5">
                                <button wire:click="$set('categoryId', null)"
                                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                                               {{ !$categoryId ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    {{ __('All') }}
                                </button>
                                @foreach($categories as $cat)
                                    <button wire:click="$set('categoryId', {{ $cat->id }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                                                   {{ $categoryId == $cat->id ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                        {{ $cat->translateAttribute('name', $locale) ?? $cat->translateAttribute('name') }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        {{-- Mobile Price --}}
                        <div>
                            <h3 class="font-semibold text-sm text-gray-900 mb-2">{{ __('Price') }} (₾)</h3>
                            <div class="flex items-center gap-2">
                                <input type="number" wire:model.live.debounce.500ms="priceMin" placeholder="{{ __('Min') }}" min="0"
                                       class="flex-1 rounded-lg border-gray-200 text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary">
                                <span class="text-gray-300">—</span>
                                <input type="number" wire:model.live.debounce.500ms="priceMax" placeholder="{{ __('Max') }}" min="0"
                                       class="flex-1 rounded-lg border-gray-200 text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                        </div>
                    </div>

                    {{-- Products Grid --}}
                    <div class="relative" wire:key="products-{{ $q }}-{{ $categoryId }}-{{ $sort }}-{{ $priceMin }}-{{ $priceMax }}-{{ $products->currentPage() }}">
                        {{-- Skeleton loading overlay --}}
                        <div wire:loading class="absolute inset-0 z-10 bg-surface">
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                                @for($s = 0; $s < 8; $s++)
                                    <div class="skeleton-card">
                                        <div class="aspect-square skeleton"></div>
                                        <div class="p-3 space-y-2">
                                            <div class="skeleton h-4 w-full"></div>
                                            <div class="skeleton h-4 w-2/3"></div>
                                            <div class="skeleton h-3 w-1/3 mt-1"></div>
                                            <div class="skeleton h-5 w-1/2 mt-2"></div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div wire:loading.remove>
                    @if($products->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-gray-400">{{ __('No products found matching your filters.') }}</p>
                            <button wire:click="clearFilters" class="mt-3 text-primary text-sm hover:underline">{{ __('Clear all filters') }}</button>
                        </div>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
