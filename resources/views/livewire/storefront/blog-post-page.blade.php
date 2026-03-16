@php
    $prefix = $locale === 'ka' ? '' : '/' . $locale;
    $title = $post->t('title', $locale);
    $content = $post->t('content', $locale);
    $excerpt = $post->t('excerpt', $locale);
    $image = $post->getFirstMediaUrl('featured');
@endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5 flex-wrap">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a wire:navigate href="{{ $prefix }}/blog" class="hover:text-primary transition">{{ __('Blog') }}</a>
            @if($post->category)
                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a wire:navigate href="{{ $prefix }}/blog/category/{{ $post->category->getTranslatedSlug($locale) }}" class="hover:text-primary transition">
                    {{ $post->category->getTranslatedName($locale) }}
                </a>
            @endif
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-600">{{ \Illuminate\Support\Str::limit($title, 40) }}</span>
        </nav>

        <div class="flex gap-8">
            {{-- Article --}}
            <article class="flex-1 min-w-0">
                {{-- Header --}}
                <header class="mb-6">
                    <div class="flex items-center gap-3 mb-3 text-sm">
                        @if($post->category)
                            <span class="text-primary font-medium">{{ $post->category->getTranslatedName($locale) }}</span>
                            <span class="text-gray-300">&middot;</span>
                        @endif
                        <span class="text-gray-400">{{ $post->published_at->format('d.m.Y') }}</span>
                        @if($post->tags->isNotEmpty())
                            <span class="text-gray-300">&middot;</span>
                            @foreach($post->tags->take(3) as $tag)
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $tag->tag }}</span>
                            @endforeach
                        @endif
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight">{{ $title }}</h1>
                </header>

                {{-- Featured Image --}}
                @if($image)
                <div class="mb-8 rounded-xl overflow-hidden">
                    <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-auto max-h-[500px] object-cover">
                </div>
                @endif

                {{-- Excerpt --}}
                @if($excerpt && trim(strip_tags($excerpt)) !== '')
                <div class="mb-6 text-lg text-gray-600 leading-relaxed border-l-4 border-primary pl-4">
                    {!! $excerpt !!}
                </div>
                @endif

                {{-- Block-based content --}}
                @php
                    $blocksField = match($locale) {
                        'en' => 'blocks_en',
                        'ru' => 'blocks_ru',
                        default => 'blocks',
                    };
                    $blocks = $post->{$blocksField} ?? $post->blocks ?? [];

                    // Fallback to legacy content field if no blocks
                    if (empty($blocks) && $content && trim(strip_tags($content)) !== '') {
                        $blocks = [['type' => 'text', 'data' => ['content' => $content]]];
                    }
                @endphp
                @if(!empty($blocks))
                    {!! \App\Services\BlockRenderer::render($blocks, $locale) !!}
                @endif

                {{-- Tags --}}
                @if($post->tags->isNotEmpty())
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm text-gray-500">{{ __('Tags') }}:</span>
                        @foreach($post->tags as $tag)
                            <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">{{ $tag->tag }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </article>

            {{-- Sidebar --}}
            <aside class="hidden lg:block shrink-0" style="width:280px">
                {{-- Recent Posts --}}
                @if($recentPosts->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-4">
                    <h3 class="font-semibold text-sm text-gray-900 mb-3">{{ __('Recent Posts') }}</h3>
                    <div class="space-y-3">
                        @foreach($recentPosts as $recent)
                            @php
                                $recentTitle = $recent->t('title', $locale);
                                $recentSlug = $recent->t('slug', $locale);
                                $recentImage = $recent->getFirstMediaUrl('featured');
                            @endphp
                            <a wire:navigate href="{{ $prefix }}/blog/{{ $recentSlug }}" class="flex gap-3 group">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                    @if($recentImage)
                                        <img src="{{ $recentImage }}" alt="{{ $recentTitle }}" class="w-full h-full object-cover" loading="lazy">
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 group-hover:text-primary transition line-clamp-2">{{ $recentTitle }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $recent->published_at->format('d.m.Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </aside>
        </div>
    </div>

    {{-- Schema.org --}}
    @php
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'datePublished' => $post->published_at->toIso8601String(),
            'dateModified' => $post->updated_at->toIso8601String(),
            'author' => ['@type' => 'Organization', 'name' => 'GN Industrial'],
        ];
        if ($image) $schema['image'] = $image;
        if ($excerpt) $schema['description'] = \Illuminate\Support\Str::limit(strip_tags($excerpt), 300);
    @endphp
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</div>
