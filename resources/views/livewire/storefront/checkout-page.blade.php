@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a wire:navigate href="{{ $prefix }}/cart" class="hover:text-primary transition">{{ __('Cart') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('Checkout') }}</span>
        </nav>

        @if($orderPlaced && $order)
            {{-- Thank You Page --}}
            <div class="max-w-3xl mx-auto py-8">
                {{-- Success Banner --}}
                <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8 flex items-start gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-green-800 mb-1">{{ __('Thank you. Your order has been received.') }}</h1>
                        <p class="text-green-600 text-sm">{{ __('We will contact you shortly to confirm delivery details.') }}</p>
                    </div>
                </div>

                {{-- Order Summary Bar --}}
                <div class="bg-white rounded-xl border border-gray-100 p-5 mb-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400 block mb-1">{{ __('Order Number') }}</span>
                            <span class="font-bold text-gray-900 text-base">#{{ $order->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1">{{ __('Date') }}</span>
                            <span class="font-medium text-gray-900">{{ $order->placed_at?->format('d.m.Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1">{{ __('Total') }}</span>
                            <span class="font-bold text-primary text-base">{{ number_format($order->total->value / 100, 2) }} ₾</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block mb-1">{{ __('Payment Method') }}</span>
                            <span class="font-medium text-gray-900">
                                @if(($order->meta['payment_method'] ?? '') === 'cod')
                                    {{ __('Cash on Delivery') }}
                                @elseif(($order->meta['payment_method'] ?? '') === 'bank_transfer')
                                    {{ __('Bank Transfer') }}
                                @else
                                    {{ $order->meta['payment_method'] ?? '-' }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Order Items --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-900 text-sm">{{ __('Order Details') }}</h3>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @foreach($order->lines as $line)
                                    <div class="px-5 py-3 flex items-center justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $line->description }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $line->identifier }} &middot; {{ __('Qty') }}: {{ $line->quantity }}
                                            </p>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 shrink-0">
                                            {{ number_format($line->sub_total->value / 100, 2) }} ₾
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 space-y-2 text-sm">
                                <div class="flex justify-between text-gray-600">
                                    <span>{{ __('Subtotal') }}</span>
                                    <span>{{ number_format($order->sub_total->value / 100, 2) }} ₾</span>
                                </div>
                                @if($order->tax_total->value > 0)
                                <div class="flex justify-between text-gray-600">
                                    <span>{{ __('Tax') }} (18%)</span>
                                    <span>{{ number_format($order->tax_total->value / 100, 2) }} ₾</span>
                                </div>
                                @endif
                                <div class="flex justify-between text-gray-600">
                                    <span>{{ __('Shipping') }}</span>
                                    <span class="text-green-600">{{ __('Free') }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                                    <span class="font-bold text-primary text-lg">{{ number_format($order->total->value / 100, 2) }} ₾</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Customer Details --}}
                    <div class="lg:col-span-1 space-y-4">
                        {{-- Billing --}}
                        @if($order->billingAddress)
                        <div class="bg-white rounded-xl border border-gray-100 p-5">
                            <h4 class="font-semibold text-gray-900 text-sm mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ __('Customer Details') }}
                            </h4>
                            <div class="text-sm text-gray-600 space-y-1.5">
                                <p class="font-medium text-gray-900">{{ $order->billingAddress->first_name }} {{ $order->billingAddress->last_name }}</p>
                                <p class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    {{ $order->billingAddress->contact_email }}
                                </p>
                                <p class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $order->billingAddress->contact_phone }}
                                </p>
                            </div>
                        </div>
                        @endif

                        {{-- Shipping Address --}}
                        @if($order->shippingAddress)
                        <div class="bg-white rounded-xl border border-gray-100 p-5">
                            <h4 class="font-semibold text-gray-900 text-sm mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ __('Delivery Address') }}
                            </h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>{{ $order->shippingAddress->line_one }}</p>
                                <p>{{ $order->shippingAddress->city }}{{ $order->shippingAddress->postcode ? ', ' . $order->shippingAddress->postcode : '' }}</p>
                                <p>{{ __('Georgia') }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Order Notes --}}
                        @if($order->notes)
                        <div class="bg-white rounded-xl border border-gray-100 p-5">
                            <h4 class="font-semibold text-gray-900 text-sm mb-2">{{ __('Order Notes') }}</h4>
                            <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="space-y-2">
                            <a wire:navigate href="{{ $prefix }}/"
                               class="block w-full bg-primary text-white text-center py-3 rounded-xl font-semibold text-sm hover:bg-primary-dark transition">
                                {{ __('Continue Shopping') }}
                            </a>
                            <a href="tel:+995593737673"
                               class="block w-full border border-gray-200 text-gray-700 text-center py-3 rounded-xl text-sm hover:border-primary hover:text-primary transition">
                                {{ __('Questions? Call us') }}: +995 593 73 76 73
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($lines->isEmpty())
            {{-- Empty Cart --}}
            <div class="text-center py-16">
                <p class="text-gray-400 mb-4">{{ __('Your cart is empty. Add products before checkout.') }}</p>
                <a wire:navigate href="{{ $prefix }}/" class="inline-block bg-primary text-white px-8 py-3 rounded-xl font-semibold text-sm hover:bg-primary-dark transition">
                    {{ __('Browse Products') }}
                </a>
            </div>

        @else
            <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Checkout') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Checkout Form --}}
                <div class="lg:col-span-2">
                    <form wire:submit="placeOrder">

                        {{-- Saved Addresses (logged-in users only) --}}
                        @if($savedAddresses->isNotEmpty())
                            <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
                                <h3 class="font-semibold text-gray-900 mb-3">{{ __('Delivery Address') }}</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($savedAddresses as $addr)
                                        <div class="p-4 rounded-xl border transition cursor-pointer {{ $selectedAddressId === $addr->id ? 'border-primary bg-primary-50/40 shadow-sm' : 'border-gray-100 hover:border-gray-200 bg-white' }}"
                                             wire:click="selectAddress({{ $addr->id }})">
                                            <div class="flex items-start gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-gray-900">{{ $addr->first_name }} {{ $addr->last_name }}</p>
                                                    <p class="text-[13px] text-gray-500 mt-0.5 leading-snug">{{ $addr->line_one }}, {{ $addr->city }}{{ $addr->postcode ? ' ' . $addr->postcode : '' }}</p>
                                                    @if($addr->contact_phone)
                                                        <p class="text-[13px] text-gray-400 mt-1">{{ $addr->contact_phone }}</p>
                                                    @endif
                                                    <div class="flex items-center gap-1.5 mt-2">
                                                        @if($addr->shipping_default)
                                                            <span class="text-[10px] font-medium bg-blue-50 text-blue-500 px-2 py-0.5 rounded-md">{{ __('Shipping') }}</span>
                                                        @endif
                                                        @if($addr->billing_default)
                                                            <span class="text-[10px] font-medium bg-green-50 text-green-500 px-2 py-0.5 rounded-md">{{ __('Billing') }}</span>
                                                        @endif
                                                        <span @click.stop="$wire.openEditAddressModal({{ $addr->id }})" class="text-[10px] font-medium bg-gray-50 text-gray-400 px-2 py-0.5 rounded-md cursor-pointer hover:bg-gray-100 hover:text-gray-600 transition ml-auto">{{ __('Edit') }}</span>
                                                        <span @click.stop="$dispatch('confirm-modal', { title: '{{ __("Delete Address") }}', message: '{{ __("This action cannot be undone.") }}', onConfirm: () => $wire.deleteAddress({{ $addr->id }}) })" class="text-[10px] font-medium bg-red-50 text-red-400 px-2 py-0.5 rounded-md cursor-pointer hover:bg-red-100 hover:text-red-500 transition">{{ __('Delete') }}</span>
                                                    </div>
                                                </div>
                                                <div class="w-5 h-5 shrink-0 mt-0.5 flex items-center justify-center {{ $selectedAddressId === $addr->id ? 'bg-primary rounded-full' : '' }}">
                                                    @if($selectedAddressId === $addr->id)
                                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" wire:click="openNewAddressModal"
                                        class="mt-3 inline-flex items-center gap-1.5 text-[13px] text-primary font-medium hover:text-primary-dark transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('Add New Address') }}
                                </button>
                            </div>
                        @endif

                        {{-- New Address Modal is rendered OUTSIDE the form, at end of component --}}

                        {{-- Contact --}}
                        <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
                            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Contact Information') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('First Name') }} *</label>
                                    <input type="text" wire:model="first_name" name="first_name" autocomplete="given-name"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="{{ __('First Name') }}">
                                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('Last Name') }} *</label>
                                    <input type="text" wire:model="last_name" name="last_name" autocomplete="family-name"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="{{ __('Last Name') }}">
                                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('Email') }} *</label>
                                    <input type="email" wire:model="email" name="email" autocomplete="email"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="email@example.com">
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('Phone') }} *</label>
                                    <input type="tel" wire:model="phone" name="phone" autocomplete="tel"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="+995 5XX XX XX XX">
                                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
                            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Delivery Address') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('Street Address') }} *</label>
                                    <input type="text" wire:model="line_one" name="address" autocomplete="street-address"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="{{ __('Street, building, apartment') }}">
                                    @error('line_one') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('City') }} *</label>
                                    <input type="text" wire:model="city" name="city" autocomplete="address-level2"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="{{ __('City') }}">
                                    @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('Postal Code') }}</label>
                                    <input type="text" wire:model="postcode" name="postcode" autocomplete="postal-code"
                                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                           placeholder="0000">
                                </div>
                            </div>
                        </div>

                        {{-- Payment --}}
                        <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
                            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Payment Method') }}</h3>
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition {{ $payment_method === 'cod' ? 'border-primary bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="payment_method" value="cod" class="text-primary focus:ring-primary">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ __('Cash on Delivery') }}</span>
                                        <p class="text-xs text-gray-400">{{ __('Pay when you receive your order') }}</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition {{ $payment_method === 'bank_transfer' ? 'border-primary bg-primary-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="payment_method" value="bank_transfer" class="text-primary focus:ring-primary">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ __('Bank Transfer') }}</span>
                                        <p class="text-xs text-gray-400">{{ __('Transfer to our bank account') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="bg-white rounded-xl border border-gray-100 p-6 mb-5">
                            <label class="block text-sm font-medium text-gray-600 mb-1.5">{{ __('Order Notes') }} ({{ __('optional') }})</label>
                            <textarea wire:model="notes" rows="3"
                                      class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                      placeholder="{{ __('Special instructions for delivery...') }}"></textarea>
                        </div>

                        {{-- Submit (mobile) --}}
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="lg:hidden w-full bg-primary text-white py-4 rounded-xl font-semibold text-sm hover:bg-primary-dark transition flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="placeOrder">{{ __('Place Order') }}</span>
                            <span wire:loading wire:target="placeOrder">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </span>
                        </button>
                    </form>
                </div>

                {{-- Order Summary Sidebar --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('Your Order') }}</h3>

                        <div class="divide-y divide-gray-50 mb-4">
                            @foreach($lines as $line)
                                @php
                                    $product = $line->purchasable->product;
                                    $name = $product->translateAttribute('name', $locale) ?? $product->translateAttribute('name');
                                    $linePrice = $line->purchasable->prices->first();
                                    $unitPrice = $linePrice ? $linePrice->price->value / 100 : 0;
                                @endphp
                                <div class="py-2.5 flex justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm text-gray-700 truncate">{{ $name }}</p>
                                        <p class="text-xs text-gray-400">× {{ $line->quantity }}</p>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 shrink-0">{{ number_format($unitPrice * $line->quantity, 2) }} ₾</span>
                                </div>
                            @endforeach
                        </div>

                        @if($cart)
                        <div class="border-t border-gray-100 pt-3 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('Subtotal') }}</span>
                                <span>{{ number_format($cart->subTotal->value / 100, 2) }} ₾</span>
                            </div>
                            @if($cart->taxTotal->value > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('Tax') }}</span>
                                <span>{{ number_format($cart->taxTotal->value / 100, 2) }} ₾</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('Shipping') }}</span>
                                <span class="text-green-600">{{ __('Free') }}</span>
                            </div>
                            <div class="border-t border-gray-100 pt-3 flex justify-between">
                                <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                                <span class="font-bold text-primary text-xl">{{ number_format($cart->total->value / 100, 2) }} ₾</span>
                            </div>
                        </div>

                        {{-- Submit (desktop) --}}
                        <button type="button"
                                wire:click="placeOrder"
                                wire:loading.attr="disabled"
                                class="hidden lg:flex w-full mt-5 bg-primary text-white py-3.5 rounded-xl font-semibold text-sm hover:bg-primary-dark transition items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="placeOrder">{{ __('Place Order') }}</span>
                            <span wire:loading wire:target="placeOrder">
                                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Address Modal (new + edit, outside all forms) --}}
    @if($showAddressModal)
        <div class="fixed inset-0 z-[98] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" wire:click="closeAddressModal"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-base font-semibold text-gray-900">{{ $editingAddrId ? __('Edit Address') : __('Add New Address') }}</h3>
                    <button type="button" wire:click="closeAddressModal" class="text-gray-300 hover:text-gray-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('First Name') }} @error('modal_addr_first_name')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                            <input type="text" wire:model="modal_addr_first_name" autocomplete="given-name" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('modal_addr_first_name') !border-red-300 @enderror" placeholder="{{ __('First Name') }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Last Name') }}</label>
                            <input type="text" wire:model="modal_addr_last_name" autocomplete="family-name" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="{{ __('Last Name') }}">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Street Address') }} @error('modal_addr_line_one')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                        <input type="text" wire:model="modal_addr_line_one" autocomplete="street-address" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('modal_addr_line_one') !border-red-300 @enderror" placeholder="{{ __('Street, building, apartment') }}">
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('City') }} @error('modal_addr_city')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                            <input type="text" wire:model="modal_addr_city" autocomplete="address-level2" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('modal_addr_city') !border-red-300 @enderror" placeholder="{{ __('City') }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Postal Code') }}</label>
                            <input type="text" wire:model="modal_addr_postcode" autocomplete="postal-code" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="0000">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Phone') }}</label>
                            <input type="tel" wire:model="modal_addr_phone" autocomplete="tel" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="+995...">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" wire:click="saveAddress" class="bg-primary text-white font-semibold px-6 py-3 rounded-xl text-sm hover:bg-primary-dark transition inline-flex items-center gap-2 disabled:opacity-60" wire:loading.attr="disabled" wire:target="saveAddress">
                            <span wire:loading.remove wire:target="saveAddress">{{ $editingAddrId ? __('Save & Use Address') : __('Save & Use Address') }}</span>
                            <span wire:loading wire:target="saveAddress">{{ __('Saving...') }}</span>
                            <svg wire:loading wire:target="saveAddress" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </button>
                        <button type="button" wire:click="closeAddressModal" class="text-sm text-gray-500 hover:text-gray-700 transition">{{ __('Cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
