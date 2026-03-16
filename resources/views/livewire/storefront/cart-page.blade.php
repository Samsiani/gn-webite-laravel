@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('Cart') }}</span>
        </nav>

        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Shopping Cart') }}</h1>

        @if($lines->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Cart Items --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    {{-- Header --}}
                    <div class="hidden md:grid grid-cols-12 gap-4 px-6 py-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <div class="col-span-6">{{ __('Product') }}</div>
                        <div class="col-span-2 text-center">{{ __('Price') }}</div>
                        <div class="col-span-2 text-center">{{ __('Quantity') }}</div>
                        <div class="col-span-2 text-right">{{ __('Total') }}</div>
                    </div>

                    {{-- Items --}}
                    <div class="divide-y divide-gray-50">
                        @foreach($lines as $line)
                            @php
                                $product = $line->purchasable->product;
                                $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
                                $image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
                                $url = $product->urls->first(fn($u) => $u->language?->code === $locale) ?? $product->urls->firstWhere('default', true);
                                $slug = $url?->slug ?? '';
                                $linePrice = $line->purchasable->prices->first();
                                $unitPrice = $linePrice ? $linePrice->price->value / 100 : 0;
                                $lineTotal = $unitPrice * $line->quantity;
                            @endphp
                            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center">
                                {{-- Product --}}
                                <div class="col-span-12 md:col-span-6 flex gap-4">
                                    <a wire:navigate href="{{ $prefix }}/product/{{ $slug }}" class="w-20 h-20 bg-gray-50 rounded-lg overflow-hidden shrink-0">
                                        @if($image)
                                            <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-contain p-2">
                                        @endif
                                    </a>
                                    <div class="min-w-0">
                                        <a wire:navigate href="{{ $prefix }}/product/{{ $slug }}" class="text-sm font-medium text-gray-900 hover:text-primary transition line-clamp-2">
                                            {{ $name }}
                                        </a>
                                        <p class="text-xs text-gray-400 mt-1">SKU: {{ $line->purchasable->sku }}</p>
                                        <button wire:click="removeItem({{ $line->id }})"
                                                class="text-xs text-red-400 hover:text-red-600 mt-1.5 flex items-center gap-1 transition md:hidden">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Unit Price --}}
                                <div class="hidden md:flex col-span-2 justify-center">
                                    <span class="text-sm text-gray-600">{{ number_format($unitPrice, 2) }} ₾</span>
                                </div>

                                {{-- Quantity --}}
                                <div class="col-span-6 md:col-span-2 flex justify-center">
                                    <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden h-9">
                                        <button wire:click="updateQuantity({{ $line->id }}, {{ $line->quantity - 1 }})"
                                                class="px-2.5 h-full text-gray-500 hover:bg-gray-50 hover:text-primary transition text-sm">−</button>
                                        <span class="w-10 text-center text-sm font-medium">{{ $line->quantity }}</span>
                                        <button wire:click="updateQuantity({{ $line->id }}, {{ $line->quantity + 1 }})"
                                                class="px-2.5 h-full text-gray-500 hover:bg-gray-50 hover:text-primary transition text-sm">+</button>
                                    </div>
                                </div>

                                {{-- Line Total --}}
                                <div class="col-span-4 md:col-span-2 flex items-center justify-end gap-3">
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($lineTotal, 2) }} ₾</span>
                                    <button wire:click="removeItem({{ $line->id }})"
                                            class="hidden md:block text-gray-300 hover:text-red-500 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                    <h3 class="font-semibold text-gray-900 mb-4">{{ __('Order Summary') }}</h3>

                    @if($cart)
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('Subtotal') }}</span>
                            <span>{{ number_format($cart->subTotal->value / 100, 2) }} ₾</span>
                        </div>
                        @if($cart->taxTotal->value > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('Tax') }} (18%)</span>
                            <span>{{ number_format($cart->taxTotal->value / 100, 2) }} ₾</span>
                        </div>
                        @endif
                        <div class="border-t border-gray-100 pt-3 flex justify-between">
                            <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                            <span class="font-bold text-primary text-xl">{{ number_format($cart->total->value / 100, 2) }} ₾</span>
                        </div>
                    </div>

                    <a wire:navigate href="{{ $prefix }}/checkout"
                       class="block w-full mt-5 bg-primary text-white text-center py-3.5 rounded-xl font-semibold text-sm hover:bg-primary-dark transition">
                        {{ __('Proceed to Checkout') }}
                    </a>
                    @endif

                    <a wire:navigate href="{{ $prefix }}/"
                       class="block w-full mt-3 text-center py-2.5 text-sm text-gray-500 hover:text-primary transition">
                        ← {{ __('Continue Shopping') }}
                    </a>
                </div>
            </div>
        </div>

        @else
            {{-- Empty Cart --}}
            <div class="text-center py-20">
                <svg class="w-20 h-20 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('Your cart is empty') }}</h2>
                <p class="text-gray-400 mb-6">{{ __('Browse our products and add items to your cart') }}</p>
                <a wire:navigate href="{{ $prefix }}/" class="inline-block bg-primary text-white px-8 py-3 rounded-xl font-semibold text-sm hover:bg-primary-dark transition">
                    {{ __('Browse Products') }}
                </a>
            </div>
        @endif
    </div>
</div>
