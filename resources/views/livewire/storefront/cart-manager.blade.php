<div class="relative" x-data="{ open: @entangle('showMiniCart') }" @click.outside="open = false">
    {{-- Cart Icon Button --}}
    <button wire:click="toggleMiniCart" aria-label="Cart" class="relative p-2 text-gray-600 hover:text-primary transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
        </svg>
        @if($cartCount > 0)
            <span class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center" style="animation: badge-pop 0.3s ease">
                {{ $cartCount > 99 ? '99+' : $cartCount }}
            </span>
        @endif
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40"
         @click="open = false"></div>

    {{-- Mini Cart Dropdown --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
         class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden origin-top-right">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ __('Cart') }} ({{ $cartCount }})</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @if($lines->isNotEmpty())
            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                @foreach($lines as $line)
                    @php
                        $locale = app()->getLocale();
                        $product = $line->purchasable->product;
                        $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
                        $image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images') ?: $product->getFirstMediaUrl('gallery');
                        $linePrice = $line->purchasable->prices->first();
                        $unitPrice = $linePrice ? number_format($linePrice->price->value / 100, 2) : '0.00';
                    @endphp
                    <div class="p-3 flex gap-3">
                        <div class="w-14 h-14 bg-gray-50 rounded-lg overflow-hidden shrink-0">
                            @if($image)
                                <img src="{{ $image }}" alt="" class="w-full h-full object-contain p-1">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 font-medium truncate">{{ $name }}</p>
                            <p class="text-xs text-gray-400">{{ $line->quantity }} × {{ $unitPrice }} ₾</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($cart)
            <div class="p-4 bg-gray-50 border-t border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                    <span class="font-bold text-primary text-lg">{{ number_format($cart->total->value / 100, 2) }} ₾</span>
                </div>
                <div class="flex gap-2">
                    <a wire:navigate href="{{ (app()->getLocale() === 'ka' ? '' : '/' . app()->getLocale()) }}/cart"
                       @click="open = false"
                       class="flex-1 text-center py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-white transition">
                        {{ __('View Cart') }}
                    </a>
                    <a wire:navigate href="{{ (app()->getLocale() === 'ka' ? '' : '/' . app()->getLocale()) }}/checkout"
                       @click="open = false"
                       class="flex-1 text-center py-2.5 rounded-xl bg-primary text-white text-sm font-medium hover:bg-primary-dark transition">
                        {{ __('Checkout') }}
                    </a>
                </div>
            </div>
            @endif
        @else
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                <p class="text-sm text-gray-400">{{ __('Your cart is empty') }}</p>
            </div>
        @endif
    </div>
</div>
