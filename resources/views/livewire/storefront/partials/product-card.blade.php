@php
    $locale = app()->getLocale();
    $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
    $variant = $product->variants->first();
    $price = $variant?->prices->first();
    $priceFormatted = $price ? number_format($price->price->value / 100, 2) : null;
    $image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images');
    $url = $product->urls->first(fn ($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
    $slug = $url?->slug ?? '';
    $prefix = $locale === 'ka' ? '' : "/{$locale}";
@endphp

<a href="{{ $prefix }}/product/{{ $slug }}" class="bg-white rounded-xl shadow-sm hover:shadow-md transition group">
    <div class="aspect-square bg-gray-100 rounded-t-xl overflow-hidden">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy"
                 class="w-full h-full object-contain p-4 group-hover:scale-105 transition duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
    </div>
    <div class="p-4">
        <h3 class="text-sm font-medium text-gray-900 line-clamp-2 group-hover:text-primary transition">{{ $name }}</h3>
        @if($priceFormatted)
            <p class="mt-2 text-lg font-bold text-primary">{{ $priceFormatted }} ₾</p>
        @endif
    </div>
</a>
