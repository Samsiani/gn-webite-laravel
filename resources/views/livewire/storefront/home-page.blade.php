<div>
    {{-- Hero --}}
    <section class="bg-primary text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold mb-4">GN Industrial</h1>
            <p class="text-xl opacity-90">{{ __('Professional Kitchen Equipment') }}</p>
        </div>
    </section>

    {{-- Categories Grid --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold mb-8">{{ __('Categories') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($categories as $category)
                @php
                    $locale = app()->getLocale();
                    $name = $category->translateAttribute('name', $locale) ?? $category->translateAttribute('name');
                    $url = $category->urls->firstWhere('default', true);
                    $slug = $url?->slug ?? Str::slug($name);
                    $prefix = $locale === 'ka' ? '' : "/{$locale}";
                @endphp
                <a href="{{ $prefix }}/category/{{ $slug }}"
                   class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-6 text-center group">
                    <div class="w-16 h-16 mx-auto mb-4 bg-primary/10 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 group-hover:text-primary transition">{{ $name }}</h3>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured Products --}}
    @if($featured->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold mb-8">{{ __('Latest Products') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($featured as $product)
                @include('livewire.storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif
</div>
