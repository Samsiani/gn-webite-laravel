@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5 flex-wrap">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            @foreach($breadcrumbs as $crumb)
                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @if($loop->last)
                    <span class="text-gray-700 font-medium">{{ $crumb->translateAttribute('name', $locale) ?? $crumb->translateAttribute('name') }}</span>
                @else
                    @php
                        $crumbUrl = $crumb->urls->first(fn($u) => $u->language?->code === $locale) ?? $crumb->urls->firstWhere('default', true);
                    @endphp
                    <a wire:navigate href="{{ $prefix }}/category/{{ $crumbUrl?->slug }}" class="hover:text-primary transition">
                        {{ $crumb->translateAttribute('name', $locale) ?? $crumb->translateAttribute('name') }}
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="flex gap-6">
            {{-- Sidebar --}}
            <aside class="hidden lg:block shrink-0 space-y-4" style="width:300px">
                {{-- Categories --}}
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 mb-3">{{ __('Categories') }}</h3>
                    <ul class="space-y-0.5">
                        <li>
                            <a wire:navigate href="{{ $prefix }}/shop"
                               class="w-full text-left py-1.5 px-2 rounded-lg text-sm transition flex items-center justify-between text-gray-600 hover:bg-gray-50">
                                <span>{{ __('All Products') }}</span>
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            @php
                                $catName = $cat->translateAttribute('name', $locale) ?? $cat->translateAttribute('name');
                                $catUrl = $cat->urls->first(fn($u) => $u->language?->code === $locale) ?? $cat->urls->firstWhere('default', true);
                                $isActive = $collection && $collection->id === $cat->id;
                            @endphp
                            <li>
                                <a wire:navigate href="{{ $prefix }}/category/{{ $catUrl?->slug }}"
                                   class="w-full text-left py-1.5 px-2 rounded-lg text-sm transition flex items-center justify-between
                                          {{ $isActive ? 'bg-primary-50 text-primary font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                    <span>{{ $catName }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Subcategories --}}
                @if($children->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 mb-3">{{ __('Subcategories') }}</h3>
                    <ul class="space-y-0.5">
                        @foreach($children as $child)
                            @php
                                $childName = $child->translateAttribute('name', $locale) ?? $child->translateAttribute('name');
                                $childUrl = $child->urls->first(fn($u) => $u->language?->code === $locale) ?? $child->urls->firstWhere('default', true);
                            @endphp
                            <li>
                                <a wire:navigate href="{{ $prefix }}/category/{{ $childUrl?->slug }}"
                                   class="block py-1.5 px-2 text-sm text-gray-600 hover:text-primary hover:bg-primary-50 rounded-lg transition">
                                    {{ $childName }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Price Filter --}}
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 mb-3">{{ __('Price') }} (₾)</h3>
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <input type="number" wire:model.live.debounce.500ms="priceMin"
                                   placeholder="{{ __('Min') }}" min="0" step="1"
                                   class="w-full rounded-lg border-gray-200 text-sm py-2 px-3 focus:border-primary focus:ring-1 focus:ring-primary [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                        </div>
                        <span class="text-gray-300">—</span>
                        <div class="flex-1">
                            <input type="number" wire:model.live.debounce.500ms="priceMax"
                                   placeholder="{{ __('Max') }}" min="0" step="1"
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
                        @if($collection)
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900">
                                {{ $collection->translateAttribute('name', $locale) ?? $collection->translateAttribute('name') }}
                            </h1>
                            <p class="text-sm text-gray-400 mt-1">{{ $products->total() }} {{ __('products') }}</p>
                        @endif
                    </div>

                    {{-- Sort --}}
                    <select wire:model.live="sort"
                            class="text-sm border border-gray-200 rounded-lg px-3 py-2 bg-white focus:border-primary focus:ring-1 focus:ring-primary">
                        <option value="latest">{{ __('Latest') }}</option>
                        <option value="price_asc">{{ __('Price: Low to High') }}</option>
                        <option value="price_desc">{{ __('Price: High to Low') }}</option>
                        <option value="name">{{ __('Name') }}</option>
                    </select>
                </div>

                {{-- Products Grid --}}
                @if($products->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                        @foreach($products as $product)
                            @include('livewire.storefront.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-20">
                        <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="text-gray-400">{{ __('No products found in this category.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
