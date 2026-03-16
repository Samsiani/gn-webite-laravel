@php
    $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
    $description = $product->translateAttribute('description', $locale) ?? $product->translateAttribute('description');
    $sku = $variant?->sku;
@endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5 flex-wrap">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            @foreach($breadcrumbs as $crumb)
                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                @php
                    $crumbName = $crumb->translateAttribute('name', $locale) ?? $crumb->translateAttribute('name');
                    $crumbUrl = $crumb->urls->first(fn($u) => $u->language?->code === $locale) ?? $crumb->urls->firstWhere('default', true);
                @endphp
                <a wire:navigate href="{{ $prefix }}/category/{{ $crumbUrl?->slug }}" class="hover:text-primary transition">{{ $crumbName }}</a>
            @endforeach
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">{{ \Illuminate\Support\Str::limit($name, 40) }}</span>
        </nav>

        {{-- Product --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

            {{-- Image Gallery --}}
            <div x-data="{ active: 0 }">
                {{-- Main Image --}}
                <div class="aspect-square bg-white rounded-xl border border-gray-100 mb-3 relative overflow-hidden">
                    @if($onSale)
                        @php $pct = round((1 - (float)str_replace(',','',$price) / (float)str_replace(',','',$comparePrice)) * 100); @endphp
                        <span class="z-10 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg shadow-sm" style="position:absolute;top:10px;right:10px">-{{ $pct }}%</span>
                    @endif
                    @if($images->isNotEmpty())
                        <div class="skeleton absolute inset-0 z-0"></div>
                        @foreach($images as $idx => $img)
                            <img src="{{ $img->getUrl('large') }}"
                                 alt="{{ $name }}"
                                 class="absolute inset-0 w-full h-full object-contain p-6 z-[1]"
                                 style="transition:opacity 0.4s ease;opacity:0"
                                 :style="active === {{ $idx }} ? 'opacity:1' : 'opacity:0'"
                                 onload="this.style.opacity=1;let s=this.parentElement.querySelector('.skeleton');if(s)s.style.display='none'">
                        @endforeach
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-50 rounded-xl">
                            <svg class="w-20 h-20 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($images->count() > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach($images as $idx => $img)
                        <button @click="active = {{ $idx }}"
                                class="aspect-square bg-white rounded-lg border overflow-hidden transition"
                                :class="active === {{ $idx }} ? 'border-primary ring-2 ring-primary/30' : 'border-gray-100 hover:border-gray-300'">
                            <img src="{{ $img->getUrl('thumb') ?: $img->getUrl() }}"
                                 alt="" loading="lazy" onload="this.classList.add('loaded')"
                                 class="w-full h-full object-contain p-1">
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3 leading-tight">{{ $name }}</h1>

                {{-- Meta --}}
                <div class="flex items-center gap-4 text-sm text-gray-400 mb-5">
                    @if($sku)
                        <span style="color:#8d8f92">SKU: {{ $sku }}</span>
                    @endif
                    @if($variant && $variant->stock > 0)
                        <span class="flex items-center gap-1 text-green-600">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg>
                            {{ __('In Stock') }}
                        </span>
                    @endif
                </div>

                {{-- Price --}}
                @if($price)
                <div class="bg-primary-50 rounded-xl p-4 mb-6 flex items-center gap-3 flex-wrap">
                    @if($onSale)
                        <span class="text-lg text-gray-400 line-through">{{ $comparePrice }} ₾</span>
                        <span class="text-3xl font-bold text-primary">{{ $price }}</span>
                        <span class="text-xl text-primary/70 ml-0.5">₾</span>
                    @else
                        <span class="text-3xl font-bold text-primary">{{ $price }}</span>
                        <span class="text-xl text-primary/70 ml-0.5">₾</span>
                    @endif
                </div>
                @endif

                {{-- Specs Table --}}
                @if(!empty($specs))
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3 text-sm uppercase tracking-wider">{{ __('Specifications') }}</h3>
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <table class="w-full text-sm specs-table">
                            @foreach($specs as $label => $value)
                            <tr class="border-b border-gray-50 last:border-0">
                                <td class="px-4 py-2.5 text-gray-500 w-2/5 font-medium">{{ $label }}</td>
                                <td class="px-4 py-2.5 text-gray-900">{{ $value }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
                @endif

                {{-- Short Description --}}
                @if($shortDescription && trim(strip_tags($shortDescription)) !== '')
                <div class="mb-6 prose prose-sm max-w-none text-gray-600 prose-headings:text-gray-900 prose-strong:text-gray-800 prose-ul:list-disc prose-ol:list-decimal prose-li:my-0.5 prose-p:my-1.5 prose-a:text-primary">
                    {!! $shortDescription !!}
                </div>
                @endif

                {{-- Add to Cart --}}
                @if($variant)
                <div class="mb-6">
                    @livewire('storefront.add-to-cart', ['variantId' => $variant->id])
                </div>
                @endif

                {{-- Contact --}}
                <div class="flex gap-3 mb-6">
                    <a href="tel:+995593737673"
                       class="flex-1 border border-gray-200 text-gray-700 text-center font-medium py-3 rounded-xl hover:border-primary hover:text-primary transition text-sm">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ __('Call') }}
                        </span>
                    </a>
                    <a href="https://wa.me/995593737673"
                       class="flex-1 border border-[#25D366] text-[#25D366] text-center font-medium py-3 rounded-xl hover:bg-[#25D366] hover:text-white transition text-sm"
                       target="_blank">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.608-1.21A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-2.253 0-4.336-.745-6.012-2.003l-.432-.323-2.732.718.73-2.667-.354-.563A9.72 9.72 0 012.25 12C2.25 6.623 6.623 2.25 12 2.25S21.75 6.623 21.75 12 17.377 21.75 12 21.75z"/></svg>
                            WhatsApp
                        </span>
                    </a>
                </div>

                {{-- Delivery Info --}}
                <div class="bg-white rounded-xl border border-gray-100 p-4 space-y-3">
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="text-gray-600">{{ __('Delivery: 4-5 business days') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="text-gray-600">{{ __('Official warranty included') }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <svg class="w-5 h-5 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span class="text-gray-600">{{ __('Payment on delivery available') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($description && trim(strip_tags($description)) !== '')
        <div class="mt-10 bg-white rounded-xl border border-gray-100 p-6 md:p-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('Description') }}</h2>
            <div class="prose max-w-none text-gray-600
                        prose-headings:text-gray-900 prose-headings:font-bold
                        prose-h2:text-xl prose-h3:text-lg prose-h4:text-base
                        prose-p:my-3 prose-p:leading-relaxed
                        prose-strong:text-gray-800 prose-strong:font-semibold
                        prose-ul:list-disc prose-ul:pl-6 prose-ul:my-3
                        prose-ol:list-decimal prose-ol:pl-6 prose-ol:my-3
                        prose-li:my-1
                        prose-a:text-primary prose-a:underline
                        prose-blockquote:border-l-primary prose-blockquote:bg-gray-50 prose-blockquote:py-1 prose-blockquote:px-4
                        prose-table:border prose-th:bg-gray-50 prose-th:px-4 prose-th:py-2 prose-td:px-4 prose-td:py-2 prose-td:border
                        prose-img:rounded-xl">
                {!! $description !!}
            </div>
        </div>
        @endif

        {{-- Related Products --}}
        @if($related->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-xl font-bold text-gray-900 mb-5">{{ __('Related Products') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
                @foreach($related as $relProduct)
                    @include('livewire.storefront.partials.product-card', ['product' => $relProduct])
                @endforeach
            </div>
        </section>
        @endif
    </div>

    {{-- Schema.org --}}
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'sku' => $sku,
            'brand' => ['@type' => 'Brand', 'name' => $product->translateAttribute('brand') ?: 'GN Industrial'],
        ];
        if ($images->isNotEmpty()) {
            $schema['image'] = $images->map(fn($i) => $i->getUrl())->toArray();
        }
        if ($description) {
            $schema['description'] = \Illuminate\Support\Str::limit(strip_tags($description), 300);
        }
        if ($price) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => 'GEL',
                'availability' => 'https://schema.org/' . ($variant && $variant->stock > 0 ? 'InStock' : 'OutOfStock'),
                'url' => url()->current(),
            ];
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
</div>
