@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div class="relative w-full" x-data="{ open: @entangle('showResults') }" @click.outside="open = false">
    {{-- Search Input --}}
    <div class="relative">
        <input type="search"
               wire:model.live.debounce.300ms="query"
               @focus="if($wire.query.length >= 3) open = true"
               placeholder="{{ __('Search products...') }}"
               class="w-full rounded-xl border border-gray-200 bg-gray-50 pl-4 pr-12 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:bg-white transition [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden">
        <div class="absolute right-1 top-1 bottom-1 flex items-center">
            @if(strlen($query) > 0)
                <button wire:click="clear" class="px-2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
            <a wire:navigate href="{{ route('search') }}{{ strlen($query) >= 3 ? '?q=' . urlencode($query) : '' }}" class="bg-primary text-white rounded-lg px-3 h-full flex items-center hover:bg-primary-dark transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </a>
        </div>
    </div>

    {{-- Loading --}}
    <div wire:loading wire:target="query" class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border border-gray-100 p-4 z-50 text-center">
        <svg class="w-5 h-5 animate-spin mx-auto text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
    </div>

    {{-- Results Dropdown --}}
    <div x-show="open" x-cloak wire:loading.remove wire:target="query"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden">

        @if($results->isNotEmpty())
            <div class="max-h-[420px] overflow-y-auto divide-y divide-gray-50">
                @foreach($results as $product)
                    @php
                        $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
                        $variant = $product->variants->first();
                        $priceObj = $variant?->prices->first();
                        $priceFormatted = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
                        $comparePriceFormatted = ($priceObj?->compare_price && $priceObj->compare_price->value > 0)
                            ? number_format($priceObj->compare_price->value / 100, 2) : null;
                        $onSale = $comparePriceFormatted && (float)str_replace(',','',$comparePriceFormatted) > (float)str_replace(',','',$priceFormatted);
                        $image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
                        $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
                        $slug = $url?->slug ?? '';
                        $sku = $variant?->sku;
                    @endphp
                    <a wire:navigate href="{{ $prefix }}/product/{{ $slug }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition" @click="open = false">
                        {{-- Image --}}
                        <div class="w-14 h-14 bg-gray-50 rounded-lg overflow-hidden shrink-0">
                            @if($image)
                                <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-contain p-1">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                        </div>
                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $name }}</p>
                            @if($sku)
                                <p style="font-size:11px;color:#8d8f92" class="mt-0.5">SKU:{{ $sku }}</p>
                            @endif
                        </div>
                        {{-- Price --}}
                        <div class="text-right shrink-0">
                            @if($priceFormatted)
                                @if($onSale)
                                    <span class="text-xs text-gray-400 line-through block">{{ $comparePriceFormatted }} ₾</span>
                                @endif
                                <span class="text-sm font-bold text-primary">{{ $priceFormatted }} ₾</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- View All --}}
            <a wire:navigate href="{{ route('search') }}?q={{ urlencode($query) }}"
               class="block text-center py-2.5 text-sm text-primary font-medium border-t border-gray-100 hover:bg-primary-50 transition"
               @click="open = false">
                {{ __('View all results') }} &rarr;
            </a>
        @elseif(strlen($query) >= 3)
            <div class="p-6 text-center">
                <svg class="w-10 h-10 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-sm text-gray-400">{{ __('No products found for') }} "{{ $query }}"</p>
            </div>
        @endif
    </div>
</div>
