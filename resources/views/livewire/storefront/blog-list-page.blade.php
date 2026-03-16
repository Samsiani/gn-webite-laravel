@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('Blog') }}</span>
        </nav>

        <h1 class="text-2xl font-bold text-gray-900 mb-6">
            {{ $activeCategory ? $activeCategory->getTranslatedName($locale) : __('Blog') }}
        </h1>

        @if($posts->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
                @php
                    $postTitle = $post->t('title', $locale);
                    $postExcerpt = $post->t('excerpt', $locale);
                    $postSlug = $post->t('slug', $locale);
                    $image = $post->getFirstMediaUrl('featured', 'thumb') ?: $post->getFirstMediaUrl('featured');
                @endphp
                <a wire:navigate href="{{ $prefix }}/blog/{{ $postSlug }}"
                   class="bg-white rounded-xl border border-gray-100 overflow-hidden group product-card block">
                    <div class="aspect-[16/10] bg-gray-100 overflow-hidden">
                        @if($image)
                            <img src="{{ $image }}" alt="{{ $postTitle }}" loading="lazy"
                                 onload="this.classList.add('loaded')"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            @if($post->category)
                                <span class="text-xs text-primary font-medium">{{ $post->category->getTranslatedName($locale) }}</span>
                                <span class="text-gray-300">&middot;</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $post->published_at->format('d.m.Y') }}</span>
                        </div>
                        <h2 class="font-semibold text-gray-900 group-hover:text-primary transition mb-2 line-clamp-2">{{ $postTitle }}</h2>
                        @if($postExcerpt)
                            <p class="text-sm text-gray-500 line-clamp-3">{{ strip_tags($postExcerpt) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">{{ $posts->links() }}</div>
        @else
            <div class="text-center py-16">
                <p class="text-gray-400">{{ __('No posts found.') }}</p>
            </div>
        @endif
    </div>
</div>
