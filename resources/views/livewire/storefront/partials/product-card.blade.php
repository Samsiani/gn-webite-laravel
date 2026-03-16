@php
    $locale = app()->getLocale();
    $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
    $variant = $product->variants->first();
    $priceObj = $variant?->prices->first();
    $priceFormatted = $priceObj ? number_format($priceObj->price->value / 100, 2) : null;
    $comparePriceFormatted = ($priceObj?->compare_price && $priceObj->compare_price->value > 0)
        ? number_format($priceObj->compare_price->value / 100, 2) : null;
    $onSale = $comparePriceFormatted && (float)str_replace(',','',$comparePriceFormatted) > (float)str_replace(',','',$priceFormatted);
    $image = $product->getFirstMediaUrl('images', 'medium') ?: $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery', 'medium') ?: $product->getFirstMediaUrl('gallery');
    $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
    $slug = $url?->slug ?? '';
    $prefix = $locale === 'ka' ? '' : "/{$locale}";
    $variantId = $variant?->id;
    $sku = $variant?->sku;
@endphp

<div class="product-card bg-white rounded-xl border border-gray-100 overflow-hidden group relative">
    {{-- Image --}}
    <a wire:navigate href="{{ $prefix }}/product/{{ $slug }}">
        <div class="aspect-square bg-white overflow-hidden relative">
            @if($image)
                <div class="skeleton absolute inset-0"></div>
                <img src="{{ $image }}" alt="{{ $name }}" loading="lazy"
                     width="300" height="300"
                     onload="this.classList.add('loaded');this.previousElementSibling.style.display='none'"
                     class="w-full h-full object-contain p-3 relative z-[1]">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gray-50">
                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
            @if($onSale)
                @php $pct = round((1 - (float)str_replace(',','',$priceFormatted) / (float)str_replace(',','',$comparePriceFormatted)) * 100); @endphp
                <span class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md">-{{ $pct }}%</span>
            @endif
        </div>
    </a>

    {{-- Info --}}
    <div class="p-3 border-t border-gray-50">
        <a wire:navigate href="{{ $prefix }}/product/{{ $slug }}">
            <h3 style="font-size:15px;min-height:2em" class="font-medium text-gray-800 line-clamp-2 group-hover:text-primary transition leading-snug">
                {{ $name }}
            </h3>
        </a>
        @if($sku)
            <p class="mt-0.5" style="font-size:11px;line-height:1;color:#444">SKU:{{ $sku }}</p>
        @endif
        <div class="mt-1.5 flex items-center justify-between gap-2">
            <div class="flex items-baseline gap-1.5 flex-wrap min-w-0">
                @if($priceFormatted)
                    @if($onSale)
                        <span class="text-xs text-gray-400 line-through">{{ $comparePriceFormatted }}</span>
                        <span class="text-base font-bold text-primary">{{ $priceFormatted }} <span class="text-xs">₾</span></span>
                    @else
                        <span class="text-base font-bold text-primary">{{ $priceFormatted }} <span class="text-xs">₾</span></span>
                    @endif
                @else
                    <span class="text-xs text-gray-400">{{ __('Contact') }}</span>
                @endif
            </div>
            @if($variantId && $priceFormatted)
                @livewire('storefront.add-to-cart-icon', ['variantId' => $variantId], key('cart-icon-' . $variantId))
            @endif
        </div>
    </div>
</div>
