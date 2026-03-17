@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    {{-- Hero Slider (database-driven) --}}
    @php $slides = \App\Models\Slide::active()->with('media')->get(); @endphp
    @if($slides->isNotEmpty())
    <section class="relative text-white overflow-hidden bg-primary-dark min-h-[600px]"
             x-data="{
                slide: 0, auto: null, total: {{ $slides->count() }}, touchX: 0,
                next() { this.slide = (this.slide + 1) % this.total },
                prev() { this.slide = (this.slide - 1 + this.total) % this.total },
                startAuto() { this.auto = setInterval(() => this.next(), 8000) },
                stopAuto() { clearInterval(this.auto) }
             }"
             x-init="startAuto()"
             @mouseenter="stopAuto()" @mouseleave="startAuto()"
             @touchstart.passive="touchX = $event.touches[0].clientX; stopAuto()"
             @touchend.passive="let diff = touchX - $event.changedTouches[0].clientX; if (Math.abs(diff) > 50) { diff > 0 ? next() : prev() } startAuto()">

        {{-- Height spacer — invisible, sets stable height from first slide's padding --}}
        <div class="invisible py-20 md:py-28" aria-hidden="true">
            <div class="max-w-[1400px] mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="text-sm mb-6">&nbsp;</div>
                        <div class="text-3xl md:text-5xl lg:text-[3.5rem] font-bold mb-5 leading-[1.15]">&nbsp;<br>&nbsp;</div>
                        <div class="text-lg mb-8">&nbsp;<br>&nbsp;</div>
                        <div class="py-3.5">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>

        @foreach($slides as $i => $s)
            @php
                $bgImage = $s->bg_type === 'image' ? $s->getFirstMediaUrl('background') : null;
                $ctaUrl = $s->cta_url ? ($s->cta_url[0] === '/' ? $prefix . $s->cta_url : $s->cta_url) : $prefix . '/shop';
                $cta2Url = $s->cta2_url ?: null;
            @endphp

            {{-- Background --}}
            <div class="absolute inset-0 pointer-events-none"
                 :style="slide === {{ $i }} ? 'transition: opacity 600ms cubic-bezier(0.4,0,0.2,1) 500ms; will-change: opacity;' : 'transition: opacity 500ms cubic-bezier(0.4,0,0.2,1) 0ms; will-change: opacity;'"
                 :class="slide === {{ $i }} ? 'opacity-100' : 'opacity-0'">
                @if($bgImage)
                    <img src="{{ $bgImage }}" alt="{{ $s->t('title', $locale) }}" class="absolute inset-0 w-full h-full object-cover" {{ $i === 0 ? 'fetchpriority="high" loading="eager"' : 'loading="lazy"' }}>
                    @if($s->overlay_color !== 'transparent' && $s->overlay_color !== 'none')
                        <div class="absolute inset-0" style="background:linear-gradient(to right, {{ $s->overlay_color ?? 'rgba(26,28,61,0.88)' }} 0%, rgba(80,82,157,0.7) 100%)"></div>
                    @endif
                @else
                    <div class="absolute inset-0 bg-gradient-to-br {{ $s->bg_gradient ?? 'from-primary via-primary-dark to-[#2d2f5e]' }}"></div>
                    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
                    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-primary-light/10 rounded-full translate-y-1/3 -translate-x-1/4"></div>
                @endif
            </div>

            {{-- Content — outer wrapper is STATIC, stays visible during exit --}}
            <div class="absolute inset-0 flex items-center pointer-events-none z-10"
                 :style="slide === {{ $i }} ? '' : 'transition: visibility 0s linear 600ms; visibility: hidden;'"
                 :class="slide === {{ $i }} ? 'visible' : ''">
                <div class="max-w-[1400px] mx-auto px-4 w-full py-20 md:py-28">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        {{-- Text column — exit: fade only, enter: fade + slide up --}}
                        <div :style="slide === {{ $i }}
                                 ? 'transition: opacity 600ms cubic-bezier(0.4,0,0.2,1) 550ms, transform 600ms cubic-bezier(0.4,0,0.2,1) 550ms; will-change: opacity, transform;'
                                 : 'transition: opacity 400ms cubic-bezier(0.4,0,0.2,1) 0ms, transform 0ms linear 400ms; will-change: opacity;'"
                             :class="slide === {{ $i }} ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 translate-y-5 pointer-events-none'">
                            @if($s->t('badge', $locale))
                            <div class="inline-flex items-center gap-2 bg-white/[0.12] rounded-full px-4 py-1.5 text-sm mb-6">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                                {{ $s->t('badge', $locale) }}
                            </div>
                            @endif
                            <h2 class="text-3xl md:text-5xl lg:text-[3.5rem] font-bold mb-5 leading-[1.15]">{{ $s->t('title', $locale) }}</h2>
                            <p class="text-lg text-white/90 mb-8 leading-relaxed max-w-lg">{{ $s->t('subtitle', $locale) }}</p>
                            <div class="flex flex-wrap gap-3">
                                @if($s->t('cta_text', $locale))
                                <a wire:navigate href="{{ $ctaUrl }}"
                                   class="bg-white text-primary font-semibold px-7 py-3.5 rounded-xl hover:bg-gray-100 transition text-sm inline-flex items-center gap-2">
                                    {{ $s->t('cta_text', $locale) }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                                @endif
                                @if($s->t('cta2_text', $locale) && $cta2Url)
                                <a href="{{ $cta2Url }}"
                                   class="border border-white/25 text-white font-semibold px-7 py-3.5 rounded-xl hover:bg-white/10 transition text-sm inline-flex items-center gap-2">
                                    {{ $s->t('cta2_text', $locale) }}
                                </a>
                                @endif
                            </div>
                        </div>
                        {{-- Stats column — 3-layer sandwich per box --}}
                        @if($s->show_stats && !empty($s->stats))
                        <div class="hidden lg:grid grid-cols-2 gap-4">
                            @foreach($s->stats as $j => $stat)
                                <div class="rounded-2xl relative overflow-hidden"
                                     :class="slide === {{ $i }} ? 'pointer-events-auto' : 'pointer-events-none'">
                                    {{-- Layer 1: Blur background — fades only, NO transform --}}
                                    <div class="absolute inset-0 rounded-2xl bg-white/[0.08]"
                                         style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);"
                                         :style="slide === {{ $i }}
                                             ? 'transition: opacity 600ms cubic-bezier(0.4,0,0.2,1) {{ 500 + $j * 80 }}ms; will-change: opacity;'
                                             : 'transition: opacity 350ms cubic-bezier(0.4,0,0.2,1) 0ms; will-change: opacity;'"
                                         :class="slide === {{ $i }} ? 'opacity-100' : 'opacity-0'"></div>
                                    {{-- Layer 2: Content — moves + fades independently --}}
                                    <div class="relative p-6"
                                         :style="slide === {{ $i }}
                                             ? 'transition: opacity 500ms cubic-bezier(0.4,0,0.2,1) {{ 600 + $j * 100 }}ms, transform 500ms cubic-bezier(0.4,0,0.2,1) {{ 600 + $j * 100 }}ms; will-change: opacity, transform;'
                                             : 'transition: opacity 350ms cubic-bezier(0.4,0,0.2,1) 0ms, transform 0ms linear 350ms; will-change: opacity;'"
                                         :class="slide === {{ $i }} ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3'">
                                        <div class="text-3xl font-bold mb-1">{{ $stat['value'] ?? '' }}</div>
                                        <div class="text-white/60 text-sm">
                                            @php
                                                $statLabel = $stat['label'] ?? '';
                                                if ($locale === 'en' && !empty($stat['label_en'])) $statLabel = $stat['label_en'];
                                                if ($locale === 'ru' && !empty($stat['label_ru'])) $statLabel = $stat['label_ru'];
                                            @endphp
                                            {{ $statLabel }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        {{-- Indicators --}}
        @if($slides->count() > 1)
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-20">
            @for($i = 0; $i < $slides->count(); $i++)
                <button @click="slide = {{ $i }}; clearInterval(auto); auto = setInterval(() => slide = (slide + 1) % {{ $slides->count() }}, 8000)"
                        aria-label="Slide {{ $i + 1 }}"
                        class="h-6 w-6 rounded-full transition-all duration-500 flex items-center justify-center"
                        :class="slide === {{ $i }} ? 'bg-white/20' : 'bg-transparent'">
                    <span class="block h-1.5 rounded-full transition-all duration-500"
                          :class="slide === {{ $i }} ? 'w-5 bg-white' : 'w-2 bg-white/30'"></span>
                </button>
            @endfor
        </div>

        {{-- Arrows --}}
        <button @click="slide = (slide + {{ $slides->count() - 1 }}) % {{ $slides->count() }}; clearInterval(auto); auto = setInterval(() => slide = (slide + 1) % {{ $slides->count() }}, 8000)"
                aria-label="{{ __('Previous Slide') }}"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition hidden md:flex">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="slide = (slide + 1) % {{ $slides->count() }}; clearInterval(auto); auto = setInterval(() => slide = (slide + 1) % {{ $slides->count() }}, 8000)"
                aria-label="{{ __('Next Slide') }}"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center text-white/70 hover:bg-white/20 hover:text-white transition hidden md:flex">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        @endif
    </section>
    @endif

    {{-- Categories Grid --}}
    @if($categories->count())
    <section class="max-w-[1400px] mx-auto px-4 -mt-6 relative z-20 fade-in-up">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($categories as $category)
                @php
                    $name = $category->translateAttribute('name', $locale) ?? $category->translateAttribute('name');
                    $url = $category->urls->first(fn($u) => $u->language?->code === $locale) ?? $category->urls->firstWhere('default', true);
                    $slug = $url?->slug ?? \Illuminate\Support\Str::slug($name);
                    $catImage = $category->getFirstMediaUrl('images', 'thumb') ?: $category->getFirstMediaUrl('images');
                @endphp
                <a wire:navigate href="{{ $prefix }}/category/{{ $slug }}"
                   class="product-card bg-white rounded-xl border border-gray-100 p-4 flex items-center gap-3 group">
                    <div class="w-11 h-11 shrink-0 flex items-center justify-center">
                        @if($catImage)
                            <img src="{{ $catImage }}" alt="{{ $name }}" loading="lazy" onload="this.classList.add('loaded')" class="w-11 h-11 object-contain">
                        @else
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h3 style="font-size:13px" class="font-semibold text-gray-800 group-hover:text-primary transition leading-tight truncate">{{ $name }}</h3>
                        <p style="font-size:11px;color:#666" class="mt-0.5">{{ $category->products_count }} {{ __('products') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Promotional Banner --}}
    <section class="max-w-[1400px] mx-auto px-4 mt-10 fade-in-up">
        <div class="bg-gradient-to-r from-[#1a1c3d] to-primary rounded-2xl overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <span class="text-primary-light text-xs font-semibold uppercase tracking-widest mb-3">{{ __('Special Offer') }}</span>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-3 leading-tight">{{ __('Free Delivery on Orders') }}</h2>
                    <p class="text-white/60 text-sm mb-6 leading-relaxed">{{ __('All orders within Tbilisi are delivered free of charge. We also offer nationwide shipping across Georgia.') }}</p>
                    <a wire:navigate href="{{ $prefix }}/shop" class="self-start bg-white text-primary font-semibold px-6 py-3 rounded-xl hover:bg-gray-100 transition text-sm">
                        {{ __('Shop Now') }}
                    </a>
                </div>
                <div class="hidden md:flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="w-28 h-28 mx-auto bg-white/10 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-14 h-14 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p class="text-white/50 text-sm">{{ __('Fast & Free') }}</p>
                        <p class="text-white font-bold text-lg">{{ __('Delivery') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- On Sale Products --}}
    @if($onSale->isNotEmpty())
    <section class="max-w-[1400px] mx-auto px-4 mt-12 fade-in-up">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('On Sale') }}</h2>
            </div>
            <a wire:navigate href="{{ $prefix }}/shop" class="text-primary text-sm font-medium hover:underline hidden sm:inline">{{ __('View All') }} &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
            @foreach($onSale as $product)
                @include('livewire.storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    {{-- Latest Products --}}
    @if($latest->isNotEmpty())
    <section class="max-w-[1400px] mx-auto px-4 mt-12 fade-in-up">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('Latest Products') }}</h2>
            </div>
            <a wire:navigate href="{{ $prefix }}/shop" class="text-primary text-sm font-medium hover:underline hidden sm:inline">{{ __('View All') }} &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
            @foreach($latest as $product)
                @include('livewire.storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    {{-- Popular Products --}}
    @if($popular->isNotEmpty())
    <section class="max-w-[1400px] mx-auto px-4 mt-12 fade-in-up">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('Popular Products') }}</h2>
            </div>
            <a wire:navigate href="{{ $prefix }}/shop" class="text-primary text-sm font-medium hover:underline hidden sm:inline">{{ __('View All') }} &rarr;</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
            @foreach($popular as $product)
                @include('livewire.storefront.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>
    @endif

    {{-- Why Choose Us --}}
    <section class="max-w-[1400px] mx-auto px-4 mt-16 fade-in-up">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ __('Why Choose GN Industrial?') }}</h2>
            <p class="text-gray-400 text-sm max-w-lg mx-auto">{{ __('We provide end-to-end solutions for the food service industry') }}</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center product-card">
                <div class="w-14 h-14 mx-auto mb-4 bg-primary-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">{{ __('Official Warranty') }}</h3>
                <p class="text-xs text-gray-400 leading-relaxed">{{ __('All products come with manufacturer warranty and after-sales service') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center product-card">
                <div class="w-14 h-14 mx-auto mb-4 bg-green-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">{{ __('Quality Certified') }}</h3>
                <p class="text-xs text-gray-400 leading-relaxed">{{ __('European quality standards, CE certified equipment') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center product-card">
                <div class="w-14 h-14 mx-auto mb-4 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">{{ __('Fast Delivery') }}</h3>
                <p class="text-xs text-gray-400 leading-relaxed">{{ __('Free delivery in Tbilisi. 4-5 day nationwide shipping') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center product-card">
                <div class="w-14 h-14 mx-auto mb-4 bg-amber-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1.5">{{ __('Expert Support') }}</h3>
                <p class="text-xs text-gray-400 leading-relaxed">{{ __('Professional consultation and technical support for all equipment') }}</p>
            </div>
        </div>
    </section>

    {{-- Blog Teaser --}}
    @if($blogPosts->isNotEmpty())
    <section class="max-w-[1400px] mx-auto px-4 mt-16 fade-in-up">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-900">{{ __('Blog') }}</h2>
            <a wire:navigate href="{{ $prefix }}/blog" class="text-primary text-sm font-medium hover:underline hidden sm:inline">{{ __('View All') }} &rarr;</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($blogPosts as $bp)
                @php
                    $bpTitle = $bp->t('title', $locale);
                    $bpSlug = $bp->t('slug', $locale);
                    $bpExcerpt = $bp->t('excerpt', $locale);
                    $bpImage = $bp->getFirstMediaUrl('featured', 'thumb') ?: $bp->getFirstMediaUrl('featured');
                @endphp
                <a wire:navigate href="{{ $prefix }}/blog/{{ $bpSlug }}"
                   class="bg-white rounded-xl border border-gray-100 overflow-hidden group product-card block">
                    <div class="aspect-[16/10] bg-gray-100 overflow-hidden">
                        @if($bpImage)
                            <img src="{{ $bpImage }}" alt="{{ $bpTitle }}" loading="lazy" onload="this.classList.add('loaded')" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-gray-400 mb-1.5">{{ $bp->published_at?->format('d.m.Y') }}</p>
                        <h3 class="font-semibold text-gray-900 group-hover:text-primary transition line-clamp-2">{{ $bpTitle }}</h3>
                        @if($bpExcerpt)
                            <p class="text-sm text-gray-500 mt-1.5 line-clamp-2">{{ strip_tags($bpExcerpt) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Consultation CTA --}}
    <section class="max-w-[1400px] mx-auto px-4 mt-16 fade-in-up">
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-0">
                <div class="md:col-span-3 p-8 md:p-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ __('Need Help Choosing Equipment?') }}</h2>
                    <p class="text-gray-500 text-sm mb-6 leading-relaxed max-w-md">
                        {{ __('Our specialists will help you select the right equipment for your business. Free consultation for restaurants, hotels, and cafes.') }}
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="tel:+995593737673" class="bg-primary text-white font-semibold px-6 py-3 rounded-xl hover:bg-primary-dark transition text-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ __('Call Now') }}
                        </a>
                        <a href="https://wa.me/995593737673" target="_blank"
                           class="border border-[#25D366] text-[#25D366] font-semibold px-6 py-3 rounded-xl hover:bg-[#25D366] hover:text-white transition text-sm inline-flex items-center gap-2">
                            WhatsApp
                        </a>
                        <a href="mailto:info@gn.ge" class="border border-gray-200 text-gray-600 font-medium px-6 py-3 rounded-xl hover:border-primary hover:text-primary transition text-sm inline-flex items-center gap-2">
                            info@gn.ge
                        </a>
                    </div>
                </div>
                <div class="md:col-span-2 bg-primary-50 p-8 md:p-12 flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto bg-primary/10 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-primary font-bold text-lg mb-1">{{ __('Visit Our Showroom') }}</p>
                        <p class="text-primary/60 text-sm">{{ __('Kaishi Street #15') }}</p>
                        <p class="text-primary/60 text-sm">{{ __('Tbilisi, Georgia') }}</p>
                        <p class="text-primary/80 font-medium text-sm mt-3">{{ __('Mon-Sat') }} 10:00 - 19:00</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust Bar --}}
    <section class="bg-white border-t border-gray-100 mt-16">
        <div class="max-w-[1400px] mx-auto px-4 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ __('Secure Payment') }}</p>
                        <p style="font-size:11px;color:#666">{{ __('Cash & Bank Transfer') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ __('Free Shipping') }}</p>
                        <p style="font-size:11px;color:#666">{{ __('Within Tbilisi') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ __('Easy Returns') }}</p>
                        <p style="font-size:11px;color:#666">{{ __('30-day return policy') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">{{ __('Support') }}</p>
                        <p style="font-size:11px;color:#666">{{ __('Professional service') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
