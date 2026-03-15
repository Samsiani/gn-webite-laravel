<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle ?? 'GN Industrial' }}</title>
    @if(isset($metaDescription))
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if(isset($canonical))
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    @if(isset($hreflangs))
        @foreach($hreflangs as $lang => $url)
            <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
        @endforeach
        @if(isset($hreflangs['ka']))
            <link rel="alternate" hreflang="x-default" href="{{ $hreflangs['ka'] }}">
        @endif
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    {{-- Header --}}
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="text-2xl font-bold text-primary">GN</span>
                    <span class="text-sm text-gray-500">Industrial</span>
                </a>

                {{-- Search --}}
                <div class="hidden md:flex flex-1 max-w-lg mx-8">
                    <form action="{{ route('search') }}" method="GET" class="w-full">
                        <input type="search" name="q"
                               placeholder="{{ __('Search products...') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary text-sm">
                    </form>
                </div>

                {{-- Language Switcher + Cart --}}
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 text-sm">
                        @php $locale = app()->getLocale(); @endphp
                        <a href="/" class="{{ $locale === 'ka' ? 'font-bold text-primary' : 'text-gray-500 hover:text-gray-700' }}">KA</a>
                        <span class="text-gray-300">|</span>
                        <a href="/en" class="{{ $locale === 'en' ? 'font-bold text-primary' : 'text-gray-500 hover:text-gray-700' }}">EN</a>
                        <span class="text-gray-300">|</span>
                        <a href="/ru" class="{{ $locale === 'ru' ? 'font-bold text-primary' : 'text-gray-500 hover:text-gray-700' }}">RU</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">GN Industrial</h3>
                    <p class="text-sm">{{ __('Professional kitchen equipment supplier') }}</p>
                    <p class="text-sm mt-2">{{ __('Tbilisi, Georgia') }}</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('Contact') }}</h4>
                    <p class="text-sm">+995 593 73 76 73</p>
                    <p class="text-sm">info@gn.ge</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">{{ __('Address') }}</h4>
                    <p class="text-sm">{{ __('Kaishi Street #15, Tbilisi, 1103, Georgia') }}</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm">
                &copy; {{ date('Y') }} GN Industrial. {{ __('All rights reserved.') }}
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
