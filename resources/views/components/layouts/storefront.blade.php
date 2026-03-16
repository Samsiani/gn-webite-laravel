<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle ?? 'GN Industrial — ' . __('Professional Kitchen Equipment') }}</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="{{ $metaDescription ?? __('Professional kitchen equipment for restaurants, hotels, and food industry.') }}">
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        body { font-family: 'Noto Sans Georgian', 'Inter', system-ui, sans-serif; }
        /* Page transitions */
        ::view-transition-old(root) {
            animation: fade-out 0.25s ease-in forwards;
        }
        ::view-transition-new(root) {
            animation: fade-in 0.35s ease-out;
        }
        @keyframes fade-out {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Top progress bar */
        .navigate-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #50529D, #7274C2);
            z-index: 9999;
            transition: width 0.3s ease;
            box-shadow: 0 0 8px rgba(80,82,157,0.5);
        }
    </style>
</head>
@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<body class="bg-surface text-gray-700 antialiased">

    {{-- Top bar (desktop only) --}}
    <div class="hidden lg:block bg-primary text-white text-xs">
        <div class="max-w-[1400px] mx-auto px-4 flex items-center justify-between h-9">
            <div class="flex items-center gap-4">
                <a href="tel:+995593737673" class="flex items-center gap-1 hover:text-primary-100 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    +995 593 73 76 73
                </a>
                <a href="mailto:info@gn.ge" class="flex items-center gap-1 hover:text-primary-100 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    info@gn.ge
                </a>
            </div>
            @livewire('storefront.language-switcher')
        </div>
    </div>

    {{-- Main Header --}}
    <header class="bg-white sticky top-0 z-50" x-data="{ mobileNav: false }">
        <div class="max-w-[1400px] mx-auto px-4">
            {{-- Desktop header --}}
            <div class="hidden lg:flex items-center justify-between h-[72px]">
                <a wire:navigate href="{{ $prefix ?? '' }}/" class="shrink-0">
                    <img src="/images/logo.png" alt="GN Industrial" class="h-12 w-auto">
                </a>
                <div class="flex flex-1 max-w-xl mx-6">
                    @livewire('storefront.live-search')
                </div>
                <div class="flex items-center gap-1">
                    {{-- Account icon --}}
                    @auth
                        <a wire:navigate href="{{ ($prefix ?? '') }}/my-account" class="relative p-2 text-gray-600 hover:text-primary transition" title="{{ __('My Account') }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </a>
                    @else
                        <div x-data="{ showLogin: false }">
                            <button @click="showLogin = true" class="relative p-2 text-gray-600 hover:text-primary transition" title="{{ __('Sign In') }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </button>
                            {{-- Login Modal --}}
                            <template x-teleport="body">
                                <div x-show="showLogin" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[98] flex items-center justify-center p-4">
                                    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="showLogin = false"></div>
                                    <div x-show="showLogin" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" @click.outside="showLogin = false">
                                        <button @click="showLogin = false" class="absolute top-4 right-4 text-gray-300 hover:text-gray-500 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                        <div class="text-center mb-6">
                                            <h3 class="text-lg font-bold text-gray-900">{{ __('Sign In') }}</h3>
                                            <p class="text-sm text-gray-400 mt-1">{{ __('Access your account, orders and addresses') }}</p>
                                        </div>
                                        <form method="POST" action="{{ ($prefix ?? '') }}/login-post" x-data="{ loading: false }" @submit="loading = true" class="space-y-4" id="header-login-form">
                                            @csrf
                                            <div>
                                                <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Email') }}</label>
                                                <input type="email" name="email" autocomplete="email" required class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="email@example.com">
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Password') }}</label>
                                                <input type="password" name="password" autocomplete="current-password" required class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="{{ __('Your password') }}">
                                            </div>
                                            <button type="submit" :disabled="loading" class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl hover:bg-primary-dark transition inline-flex items-center justify-center gap-2 disabled:opacity-60">
                                                <span x-show="!loading">{{ __('Sign In') }}</span>
                                                <span x-show="loading" class="flex items-center gap-2">{{ __('Signing in...') }} <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg></span>
                                            </button>
                                        </form>
                                        <p class="text-center text-sm text-gray-500 mt-4">
                                            {{ __("Don't have an account?") }}
                                            <a wire:navigate href="{{ ($prefix ?? '') }}/register" @click="showLogin = false" class="text-primary font-medium hover:text-primary-dark transition">{{ __('Create one') }}</a>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endauth
                    @livewire('storefront.cart-manager')
                </div>
            </div>
            {{-- Mobile header: logo | search | hamburger --}}
            <div class="flex lg:hidden items-center gap-2.5 h-14">
                <a wire:navigate href="{{ $prefix ?? '' }}/" class="shrink-0">
                    <img src="/images/logo.png" alt="GN Industrial" class="h-7 w-auto">
                </a>
                <div class="flex-1 min-w-0">
                    @livewire('storefront.live-search', [], key('mobile-search'))
                </div>
                <button @click="mobileNav = true" aria-label="Menu" class="shrink-0 p-1.5 text-gray-500 hover:text-primary transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
            </div>
        </div>
        {{-- Desktop nav bar --}}
        <div class="hidden lg:block" style="position:relative;border-top:1px solid rgba(0,0,0,0.05)">
            @livewire('storefront.navigation')
        </div>
        {{-- Mobile drawer (controlled by header's x-data) --}}
        @include('livewire.storefront.partials.mobile-drawer')
    </header>

    {{-- Main Content --}}
    <main class="pb-16 lg:pb-0">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-primary text-white mt-16">
        <div class="max-w-[1400px] mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- Brand --}}
                <div>
                    <div class="mb-4">
                        <img src="/images/logo.png" alt="GN Industrial" class="h-10 w-auto" style="filter:brightness(0) invert(1)">
                    </div>
                    <p class="text-white/70 text-sm leading-relaxed">{{ __('Professional kitchen equipment for restaurants, hotels, and food industry.') }}</p>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider">{{ __('Products') }}</h3>
                    <ul class="space-y-2 text-sm text-white/70">
                        @if(isset($categories))
                            @foreach($categories->take(6) as $cat)
                                @php
                                    $catName = $cat->translateAttribute('name', app()->getLocale()) ?? $cat->translateAttribute('name');
                                @endphp
                                <li><a href="#" class="hover:text-white transition">{{ $catName }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider">{{ __('Contact') }}</h3>
                    <ul class="space-y-3 text-sm text-white/70">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:+995593737673" class="hover:text-white transition">+995 593 73 76 73</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:info@gn.ge" class="hover:text-white transition">info@gn.ge</a>
                        </li>
                    </ul>
                </div>

                {{-- Address --}}
                <div>
                    <h3 class="font-semibold mb-4 text-sm uppercase tracking-wider">{{ __('Address') }}</h3>
                    <div class="text-sm text-white/70 space-y-2">
                        <p class="flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ __('Kaishi Street #15, Tbilisi, 1103, Georgia') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/20 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between text-sm text-white/50">
                <p>&copy; {{ date('Y') }} GN Industrial. {{ __('All rights reserved.') }}</p>
            </div>
        </div>
    </footer>

    {{-- Mobile bottom navbar --}}
    <nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-100 lg:hidden" style="padding-bottom: env(safe-area-inset-bottom, 0px);">
        <div class="flex items-center justify-around h-14">
            @php
                $currentPath = request()->path();
                $isHome = $currentPath === '/' || $currentPath === '' || $currentPath === 'en' || $currentPath === 'ru';
                $isShop = str_contains($currentPath, 'shop') || str_contains($currentPath, 'category') || str_contains($currentPath, 'product');
                $isCart = str_contains($currentPath, 'cart') || str_contains($currentPath, 'checkout');
                $isAccount = str_contains($currentPath, 'my-account') || str_contains($currentPath, 'login') || str_contains($currentPath, 'register');
            @endphp
            <a wire:navigate href="{{ $prefix ?? '' }}/" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ $isHome ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="{{ $isHome ? '2' : '1.5' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                <span class="text-[10px] font-medium">{{ __('Home') }}</span>
            </a>
            <a wire:navigate href="{{ ($prefix ?? '') }}/shop" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ $isShop ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="{{ $isShop ? '2' : '1.5' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span class="text-[10px] font-medium">{{ __('Shop') }}</span>
            </a>
            <a wire:navigate href="{{ ($prefix ?? '') }}/cart" class="flex flex-col items-center gap-0.5 px-3 py-1 relative {{ $isCart ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="{{ $isCart ? '2' : '1.5' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <span class="text-[10px] font-medium">{{ __('Cart') }}</span>
            </a>
            <a wire:navigate href="{{ ($prefix ?? '') }}/{{ auth()->check() ? 'my-account' : 'login' }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ $isAccount ? 'text-primary' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="{{ $isAccount ? '2' : '1.5' }}" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-[10px] font-medium">{{ __('Account') }}</span>
            </a>
        </div>
    </nav>

    {{-- Loading progress bar --}}
    <div id="nav-progress" class="navigate-loading-bar" style="width:0;opacity:0"></div>

    @livewireScripts
    @livewireScriptConfig
    <script>
        /* ── Progress bar ── */
        document.addEventListener('livewire:navigate', () => {
            const bar = document.getElementById('nav-progress');
            bar.style.opacity = '1';
            bar.style.width = '70%';
        });
        document.addEventListener('livewire:navigated', () => {
            const bar = document.getElementById('nav-progress');
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => { bar.style.width = '0'; }, 300);
            }, 150);
            initPageEffects();
        });

        /* ── Scroll fade-in & lazy images ── */
        function initPageEffects() {
            // Fade-in on scroll
            const faders = document.querySelectorAll('.fade-in-up:not(.visible)');
            if (faders.length) {
                const obs = new IntersectionObserver((entries) => {
                    entries.forEach((e, i) => {
                        if (e.isIntersecting) {
                            setTimeout(() => e.target.classList.add('visible'), i * 80);
                            obs.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });
                faders.forEach(el => obs.observe(el));
            }

            // Lazy image onload
            document.querySelectorAll('img[loading="lazy"]:not(.loaded)').forEach(img => {
                if (img.complete && img.naturalWidth) {
                    img.classList.add('loaded');
                    const skel = img.previousElementSibling;
                    if (skel && skel.classList.contains('skeleton')) skel.style.display = 'none';
                }
            });
        }

        // Run on first load
        document.addEventListener('DOMContentLoaded', initPageEffects);
    </script>

    {{-- Global confirm modal --}}
    <div x-data="confirmModal()" x-cloak
         @confirm-modal.window="open($event.detail)"
         x-show="show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[99] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" @click="cancel()"></div>
        <div x-show="show" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center">
            <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 mb-1" x-text="title"></h3>
            <p class="text-sm text-gray-400 mb-6" x-text="message"></p>
            <div class="flex items-center justify-center gap-3">
                <button @click="cancel()" class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">{{ __('Cancel') }}</button>
                <button @click="confirm()" class="px-5 py-2.5 text-sm font-medium text-white bg-red-500 rounded-xl hover:bg-red-600 transition">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
    <script>
        function confirmModal() {
            return {
                show: false,
                title: '',
                message: '',
                onConfirm: null,
                open(detail) {
                    this.title = detail.title || 'Are you sure?';
                    this.message = detail.message || '';
                    this.onConfirm = detail.onConfirm || null;
                    this.show = true;
                },
                confirm() {
                    if (this.onConfirm) this.onConfirm();
                    this.show = false;
                },
                cancel() {
                    this.show = false;
                }
            }
        }
    </script>
</body>
</html>
