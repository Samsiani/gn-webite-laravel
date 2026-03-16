@php
    $locale = app()->getLocale();
    $prefix = $locale === 'ka' ? '' : '/' . $locale;
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->join('');
@endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('My Account') }}</span>
        </nav>

        {{-- Welcome header --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-primary-dark flex items-center justify-center text-white font-bold text-lg shadow-sm">
                        {{ $initials }}
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">{{ $user->name }}</h1>
                        <div class="flex items-center gap-3 mt-0.5">
                            <span class="text-sm text-gray-400">{{ $user->email }}</span>
                            @if($user->phone)
                                <span class="text-gray-200">|</span>
                                <span class="text-sm text-gray-400">{{ $user->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg">
                        {{ __('Member since') }} {{ $user->created_at->format('M Y') }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                            {{ __('Sign Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <button wire:click="setTab('orders')" class="product-card bg-white rounded-xl border border-gray-100 p-5 text-left cursor-pointer {{ $tab === 'orders' ? 'border-primary/40 !shadow-md !shadow-primary/10' : '' }}">
                <div class="flex items-center gap-3.5">
                    <div class="w-[35px] h-[35px] rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                    <div>
                        <p class="text-[18px] font-bold text-gray-900">{{ $orders->count() }}</p>
                        <p class="text-[15px] font-medium text-gray-500 mt-0.5">{{ __('Orders') }}</p>
                    </div>
                </div>
            </button>
            <button wire:click="setTab('addresses')" class="product-card bg-white rounded-xl border border-gray-100 p-5 text-left cursor-pointer {{ $tab === 'addresses' ? 'border-primary/40 !shadow-md !shadow-primary/10' : '' }}">
                <div class="flex items-center gap-3.5">
                    <div class="w-[35px] h-[35px] rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[18px] font-bold text-gray-900">{{ $addresses->count() }}</p>
                        <p class="text-[15px] font-medium text-gray-500 mt-0.5">{{ __('Addresses') }}</p>
                    </div>
                </div>
            </button>
            <button wire:click="setTab('profile')" class="product-card bg-white rounded-xl border border-gray-100 p-5 text-left cursor-pointer {{ $tab === 'profile' ? 'border-primary/40 !shadow-md !shadow-primary/10' : '' }}">
                <div class="flex items-center gap-3.5">
                    <div class="w-[35px] h-[35px] rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-[18px] font-bold text-gray-900">{{ __('Profile') }}</p>
                        <p class="text-[15px] font-medium text-gray-500 mt-0.5">{{ __('Edit details') }}</p>
                    </div>
                </div>
            </button>
            <button wire:click="setTab('password')" class="product-card bg-white rounded-xl border border-gray-100 p-5 text-left cursor-pointer {{ $tab === 'password' ? 'border-primary/40 !shadow-md !shadow-primary/10' : '' }}">
                <div class="flex items-center gap-3.5">
                    <div class="w-[35px] h-[35px] rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <p class="text-[18px] font-bold text-gray-900">{{ __('Security') }}</p>
                        <p class="text-[15px] font-medium text-gray-500 mt-0.5">{{ __('Password') }}</p>
                    </div>
                </div>
            </button>
        </div>

        {{-- Tab Content --}}

        {{-- ═══ ORDERS ═══ --}}
        @if($tab === 'orders')
            @if($orders->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 py-16 text-center">
                    <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('No orders yet') }}</h3>
                    <p class="text-sm text-gray-400 mb-5">{{ __('Your order history will appear here after your first purchase.') }}</p>
                    <a wire:navigate href="{{ $prefix }}/shop" class="inline-flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        {{ __('Start Shopping') }}
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($orders as $order)
                        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                            {{-- Order header --}}
                            <div class="px-5 py-3.5 bg-gray-50/60 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-gray-900">#{{ $order->reference ?? $order->id }}</span>
                                    @php
                                        $statusMap = [
                                            'pending' => ['bg-amber-50 text-amber-600 border-amber-200', __('Pending')],
                                            'awaiting-payment' => ['bg-amber-50 text-amber-600 border-amber-200', __('Awaiting Payment')],
                                            'payment-received' => ['bg-green-50 text-green-600 border-green-200', __('Paid')],
                                            'dispatched' => ['bg-blue-50 text-blue-600 border-blue-200', __('Dispatched')],
                                            'cancelled' => ['bg-red-50 text-red-500 border-red-200', __('Cancelled')],
                                        ];
                                        [$statusClass, $statusLabel] = $statusMap[$order->status] ?? ['bg-gray-50 text-gray-500 border-gray-200', ucfirst($order->status)];
                                    @endphp
                                    <span class="text-xs px-2.5 py-1 rounded-lg font-medium border {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-400">
                                    <span>{{ $order->placed_at?->format('d.m.Y, H:i') }}</span>
                                    @if($order->meta['payment_method'] ?? false)
                                        <span class="hidden sm:inline">{{ $order->meta['payment_method'] === 'cod' ? __('Cash on Delivery') : __('Bank Transfer') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Order items --}}
                            <div class="px-5 py-3 divide-y divide-gray-50">
                                @foreach($order->lines->take(4) as $line)
                                    <div class="flex items-center justify-between py-2 gap-4">
                                        <div class="min-w-0">
                                            <p class="text-sm text-gray-700 truncate">{{ $line->description }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">{{ __('Qty') }}: {{ $line->quantity }}{{ $line->identifier ? ' · ' . $line->identifier : '' }}</p>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 shrink-0">{{ number_format($line->sub_total->value / 100, 2) }} ₾</span>
                                    </div>
                                @endforeach
                                @if($order->lines->count() > 4)
                                    <p class="text-xs text-gray-400 py-2">+{{ $order->lines->count() - 4 }} {{ __('more items') }}</p>
                                @endif
                            </div>

                            {{-- Order total --}}
                            <div class="px-5 py-3 bg-gray-50/40 border-t border-gray-100 flex items-center justify-between">
                                @if($order->shippingAddress)
                                    <span class="text-xs text-gray-400 hidden sm:inline">
                                        <svg class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                        {{ $order->shippingAddress->city }}{{ $order->shippingAddress->line_one ? ', ' . Str::limit($order->shippingAddress->line_one, 30) : '' }}
                                    </span>
                                @else
                                    <span></span>
                                @endif
                                <span class="text-base font-bold text-primary">{{ number_format($order->total->value / 100, 2) }} ₾</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ═══ ADDRESSES ═══ --}}
        @elseif($tab === 'addresses')
            @if($showAddressForm)
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="font-semibold text-gray-900">{{ $editingAddressId ? __('Edit Address') : __('New Address') }}</h2>
                        <button wire:click="resetAddressForm" class="text-sm text-gray-400 hover:text-gray-600 transition">{{ __('Cancel') }}</button>
                    </div>
                    <form wire:submit.prevent="saveAddress" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('First Name') }} @error('addr_first_name')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                                <input type="text" wire:model="addr_first_name" name="first_name" autocomplete="given-name" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('addr_first_name') !border-red-300 @enderror" placeholder="{{ __('First Name') }}">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Last Name') }}</label>
                                <input type="text" wire:model="addr_last_name" name="last_name" autocomplete="family-name" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="{{ __('Last Name') }}">
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Street Address') }} @error('addr_line_one')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                            <input type="text" wire:model="addr_line_one" name="address" autocomplete="street-address" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('addr_line_one') !border-red-300 @enderror" placeholder="{{ __('Street, building, apartment') }}">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('City') }} @error('addr_city')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                                <input type="text" wire:model="addr_city" name="city" autocomplete="address-level2" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('addr_city') !border-red-300 @enderror" placeholder="{{ __('City') }}">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Postal Code') }}</label>
                                <input type="text" wire:model="addr_postcode" name="postcode" autocomplete="postal-code" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="0000">
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Phone') }}</label>
                                <input type="tel" wire:model="addr_phone" name="phone" autocomplete="tel" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="+995...">
                            </div>
                        </div>
                        <div class="flex items-center gap-5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="addr_shipping_default" class="rounded text-primary focus:ring-primary">
                                <span class="text-sm text-gray-600">{{ __('Default shipping') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="addr_billing_default" class="rounded text-primary focus:ring-primary">
                                <span class="text-sm text-gray-600">{{ __('Default billing') }}</span>
                            </label>
                        </div>
                        <button type="submit" class="bg-primary text-white font-semibold px-6 py-3 rounded-xl text-sm hover:bg-primary-dark transition">{{ __('Save Address') }}</button>
                    </form>
                </div>
            @elseif($addresses->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 py-16 text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">{{ __('No saved addresses') }}</h3>
                    <p class="text-sm text-gray-400 mb-5">{{ __('Add an address to speed up checkout next time.') }}</p>
                    <button wire:click="$set('showAddressForm', true)" class="inline-flex items-center gap-1.5 bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-dark transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add Address') }}
                    </button>
                </div>
            @else
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">{{ __('Saved Addresses') }}</h2>
                    <button wire:click="$set('showAddressForm', true)" class="text-sm text-primary font-medium hover:text-primary-dark transition inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add New') }}
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($addresses as $address)
                        <div class="bg-white rounded-xl border border-gray-100 p-5 relative group">
                            <div class="flex items-center gap-2 mb-3">
                                @if($address->shipping_default)
                                    <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-lg font-medium">{{ __('Shipping') }}</span>
                                @endif
                                @if($address->billing_default)
                                    <span class="text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-lg font-medium">{{ __('Billing') }}</span>
                                @endif
                                @if(!$address->shipping_default && !$address->billing_default)
                                    <span class="text-xs bg-gray-50 text-gray-400 px-2 py-0.5 rounded-lg">{{ __('Other') }}</span>
                                @endif
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $address->first_name }} {{ $address->last_name }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $address->line_one }}</p>
                            <p class="text-sm text-gray-500">{{ $address->city }}{{ $address->postcode ? ', ' . $address->postcode : '' }}</p>
                            @if($address->contact_phone)
                                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    {{ $address->contact_phone }}
                                </p>
                            @endif
                            <div class="flex items-center gap-3 mt-4 pt-3 border-t border-gray-50">
                                <button wire:click="editAddress({{ $address->id }})" class="text-xs text-primary font-medium hover:text-primary-dark transition">{{ __('Edit') }}</button>
                                <button @click="$dispatch('confirm-modal', { title: '{{ __('Delete Address') }}', message: '{{ __('This action cannot be undone.') }}', onConfirm: () => $wire.deleteAddress({{ $address->id }}) })" class="text-xs text-red-400 hover:text-red-600 transition">{{ __('Delete') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ═══ PROFILE ═══ --}}
        @elseif($tab === 'profile')
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-5">{{ __('Profile Details') }}</h2>

                @if(session('profile_success'))
                    <div class="bg-green-50 border border-green-100 text-green-600 text-sm rounded-xl px-4 py-3 mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('profile_success') }}
                    </div>
                @endif

                <form wire:submit.prevent="updateProfile" class="space-y-4 max-w-xl">
                    <div>
                        <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Full Name') }} @error('profile_name')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                        <input type="text" wire:model="profile_name" name="name" autocomplete="name" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('profile_name') !border-red-300 @enderror">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Email') }} @error('profile_email')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                            <input type="email" wire:model="profile_email" name="email" autocomplete="email" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('profile_email') !border-red-300 @enderror">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Phone') }}</label>
                            <input type="tel" wire:model="profile_phone" name="phone" autocomplete="tel" class="w-full rounded-xl text-sm py-3 focus:border-primary">
                        </div>
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="bg-primary text-white font-semibold px-6 py-3 rounded-xl text-sm hover:bg-primary-dark transition inline-flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="updateProfile">{{ __('Save Changes') }}</span>
                        <span wire:loading wire:target="updateProfile">{{ __('Saving...') }}</span>
                        <svg wire:loading wire:target="updateProfile" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </button>
                </form>
            </div>

        {{-- ═══ PASSWORD ═══ --}}
        @elseif($tab === 'password')
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-1">{{ __('Change Password') }}</h2>
                <p class="text-sm text-gray-400 mb-5">{{ __('Keep your account secure with a strong password.') }}</p>

                @if(session('password_success'))
                    <div class="bg-green-50 border border-green-100 text-green-600 text-sm rounded-xl px-4 py-3 mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ session('password_success') }}
                    </div>
                @endif

                <form wire:submit.prevent="updatePassword" class="space-y-4 max-w-xl">
                    <div>
                        <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Current Password') }} @error('current_password')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                        <input type="password" wire:model="current_password" autocomplete="current-password" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('current_password') !border-red-300 @enderror" placeholder="{{ __('Enter current password') }}">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('New Password') }} @error('new_password')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                            <input type="password" wire:model="new_password" autocomplete="new-password" class="w-full rounded-xl text-sm py-3 focus:border-primary @error('new_password') !border-red-300 @enderror" placeholder="{{ __('Min 6 characters') }}">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">{{ __('Confirm Password') }}</label>
                            <input type="password" wire:model="new_password_confirmation" autocomplete="new-password" class="w-full rounded-xl text-sm py-3 focus:border-primary" placeholder="{{ __('Repeat new password') }}">
                        </div>
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="bg-primary text-white font-semibold px-6 py-3 rounded-xl text-sm hover:bg-primary-dark transition inline-flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="updatePassword">{{ __('Update Password') }}</span>
                        <span wire:loading wire:target="updatePassword">{{ __('Updating...') }}</span>
                        <svg wire:loading wire:target="updatePassword" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </button>
                </form>
            </div>
        @endif

    </div>
</div>
