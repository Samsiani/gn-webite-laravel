<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary">{{ __('Home') }}</a>
            @if($product->collections->first())
                <span class="mx-2">/</span>
                @php
                    $cat = $product->collections->first();
                    $catName = $cat->translateAttribute('name', $locale) ?? $cat->translateAttribute('name');
                @endphp
                <a href="#" class="hover:text-primary">{{ $catName }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $product->translateAttribute('name', $locale) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Image Gallery --}}
            <div>
                @php $images = $product->getMedia('images'); @endphp
                @if($images->isNotEmpty())
                    <div class="aspect-square bg-white rounded-xl shadow-sm overflow-hidden">
                        <img src="{{ $images->first()->getUrl() }}"
                             alt="{{ $product->translateAttribute('name', $locale) }}"
                             class="w-full h-full object-contain p-8">
                    </div>
                    @if($images->count() > 1)
                    <div class="grid grid-cols-4 gap-2 mt-4">
                        @foreach($images->skip(1) as $img)
                        <div class="aspect-square bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer hover:ring-2 ring-primary">
                            <img src="{{ $img->getUrl('thumb') ?: $img->getUrl() }}"
                                 alt="" loading="lazy"
                                 class="w-full h-full object-contain p-2">
                        </div>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="aspect-square bg-gray-100 rounded-xl flex items-center justify-center">
                        <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ $product->translateAttribute('name', $locale) }}
                </h1>

                @if($price)
                <div class="text-3xl font-bold text-primary mb-6">
                    {{ $price }} ₾
                </div>
                @endif

                @php $sku = $product->variants->first()?->sku; @endphp
                @if($sku)
                <p class="text-sm text-gray-500 mb-6">SKU: {{ $sku }}</p>
                @endif

                {{-- Specifications --}}
                @php
                    $specs = [];
                    $specFields = ['brand', 'power', 'voltage', 'dimensions', 'weight', 'capacity', 'control_type', 'body_material', 'power_source'];
                    $specLabels = [
                        'brand' => __('Brand'),
                        'power' => __('Power'),
                        'voltage' => __('Voltage'),
                        'dimensions' => __('Dimensions'),
                        'weight' => __('Weight'),
                        'capacity' => __('Capacity'),
                        'control_type' => __('Control Type'),
                        'body_material' => __('Body Material'),
                        'power_source' => __('Power Source'),
                    ];
                    foreach ($specFields as $field) {
                        $val = $product->translateAttribute($field, $locale) ?? $product->translateAttribute($field);
                        if ($val) {
                            $specs[$specLabels[$field]] = $val;
                        }
                    }
                @endphp

                @if(!empty($specs))
                <div class="border rounded-lg overflow-hidden mb-8">
                    <table class="w-full text-sm">
                        @foreach($specs as $label => $value)
                        <tr class="border-b last:border-0">
                            <td class="px-4 py-3 bg-gray-50 font-medium text-gray-600 w-1/3">{{ $label }}</td>
                            <td class="px-4 py-3">{{ $value }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
                @endif

                {{-- Description --}}
                @php $description = $product->translateAttribute('description', $locale); @endphp
                @if($description)
                <div class="prose max-w-none text-gray-700">
                    {!! $description !!}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Schema.org --}}
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Product",
        "name": "{{ $product->translateAttribute('name', $locale) }}",
        "sku": "{{ $product->variants->first()?->sku }}",
        @if($product->getFirstMediaUrl('images'))
        "image": "{{ $product->getFirstMediaUrl('images') }}",
        @endif
        @if($price)
        "offers": {
            "@type": "Offer",
            "price": "{{ $price }}",
            "priceCurrency": "GEL",
            "availability": "https://schema.org/InStock",
            "url": "{{ url()->current() }}"
        },
        @endif
        "brand": {
            "@type": "Brand",
            "name": "{{ $product->translateAttribute('brand') ?: 'GN Industrial' }}"
        }
    }
    </script>
</div>
