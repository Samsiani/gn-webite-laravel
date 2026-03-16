@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-[1400px] mx-auto px-4 py-6">

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-1.5">
            <a wire:navigate href="{{ $prefix }}/" class="hover:text-primary transition">{{ __('Home') }}</a>
            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700 font-medium">{{ __('Contact') }}</span>
        </nav>

        {{-- Page Header --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Contact Us') }}</h1>
            <p class="text-gray-500 text-sm max-w-xl mx-auto">{{ __('Have a question or need assistance? We\'d love to hear from you.') }}</p>
        </div>

        {{-- Info Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
            {{-- Phone --}}
            <a href="tel:+995593737673" class="product-card bg-white rounded-xl border border-gray-100 p-5 flex items-start gap-4">
                <div class="bg-primary-50 rounded-2xl w-14 h-14 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">{{ __('Phone') }}</p>
                    <p class="text-sm font-semibold text-gray-900">+995 593 73 76 73</p>
                </div>
            </a>

            {{-- Email --}}
            <a href="mailto:info@gn.ge" class="product-card bg-white rounded-xl border border-gray-100 p-5 flex items-start gap-4">
                <div class="bg-green-50 rounded-2xl w-14 h-14 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">{{ __('Email') }}</p>
                    <p class="text-sm font-semibold text-gray-900">info@gn.ge</p>
                </div>
            </a>

            {{-- Address --}}
            <div class="product-card bg-white rounded-xl border border-gray-100 p-5 flex items-start gap-4">
                <div class="bg-blue-50 rounded-2xl w-14 h-14 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">{{ __('Address') }}</p>
                    <p class="text-sm font-semibold text-gray-900">{{ __('Kaishi St #15, Tbilisi') }}</p>
                </div>
            </div>

            {{-- Working Hours --}}
            <div class="product-card bg-white rounded-xl border border-gray-100 p-5 flex items-start gap-4">
                <div class="bg-amber-50 rounded-2xl w-14 h-14 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">{{ __('Working Hours') }}</p>
                    <p class="text-sm font-semibold text-gray-900">{{ __('Mon-Sat: 10:00 - 19:00') }}</p>
                </div>
            </div>
        </div>

        {{-- Map + Contact Form --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

            {{-- Google Map --}}
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden" style="min-height: 480px;">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2978.5!2d44.8328!3d41.7217!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDHCsDQzJzE4LjEiTiA0NMKwNDknNTguMSJF!5e0!3m2!1sen!2sge!4v1"
                    width="100%" height="100%" style="border:0; min-height: 480px;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

            {{-- Contact Form --}}
            <div class="bg-white rounded-xl border border-gray-100 p-6">
                @if($submitted)
                    {{-- Success State --}}
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-14 h-14 bg-green-50 rounded-full flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1.5">{{ __('Message Sent Successfully!') }}</h3>
                        <p class="text-sm text-gray-400 mb-6 max-w-xs">{{ __('Thank you for reaching out. We\'ll get back to you as soon as possible.') }}</p>
                        <button wire:click="resetForm"
                                class="inline-flex items-center gap-1.5 text-primary font-medium text-sm hover:text-primary-dark transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            {{ __('Send Another Message') }}
                        </button>
                    </div>
                @else
                    <h3 class="font-semibold text-gray-900 mb-5">{{ __('Send us a message') }}</h3>
                    <form wire:submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Name') }} @error('name')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                                <input type="text" wire:model.blur="name" name="name" autocomplete="name"
                                       class="w-full rounded-xl text-sm py-3 focus:border-primary @error('name') !border-red-300 @enderror"
                                       placeholder="{{ __('Your name') }}">
                            </div>
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Email') }} @error('email')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                                <input type="email" wire:model.blur="email" name="email" autocomplete="email"
                                       class="w-full rounded-xl text-sm py-3 focus:border-primary @error('email') !border-red-300 @enderror"
                                       placeholder="email@example.com">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Phone') }}</label>
                                <input type="tel" wire:model="phone" name="phone" autocomplete="tel"
                                       class="w-full rounded-xl text-sm py-3 focus:border-primary"
                                       placeholder="+995 5XX XX XX XX">
                            </div>
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Subject') }} @error('subject')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                                <input type="text" wire:model.blur="subject"
                                       class="w-full rounded-xl text-sm py-3 focus:border-primary @error('subject') !border-red-300 @enderror"
                                       placeholder="{{ __('How can we help?') }}">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Message') }} @error('message')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                            <textarea wire:model.blur="message" rows="5"
                                      class="w-full rounded-xl text-sm py-3 focus:border-primary @error('message') !border-red-300 @enderror"
                                      placeholder="{{ __('Write your message here...') }}"></textarea>
                        </div>

                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submitForm"
                                class="relative bg-primary text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-primary-dark transition inline-flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="submitForm">{{ __('Send Message') }}</span>
                            <span wire:loading wire:target="submitForm">{{ __('Sending...') }}</span>
                            <svg wire:loading wire:target="submitForm" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Social Bar --}}
        <div class="bg-white rounded-xl border border-gray-100 p-6 text-center">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Follow us on social media') }}</h3>
            <div class="flex items-center justify-center gap-4">
                {{-- Facebook --}}
                <a href="https://www.facebook.com/gn.ge.official" target="_blank" rel="noopener"
                   class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-blue-600 hover:text-white transition" title="Facebook">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
                {{-- Instagram --}}
                <a href="https://www.instagram.com/gn.ge.official" target="_blank" rel="noopener"
                   class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-gradient-to-br hover:from-purple-600 hover:via-pink-500 hover:to-orange-400 hover:text-white transition" title="Instagram">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                </a>
                {{-- TikTok --}}
                <a href="https://www.tiktok.com/@gn.ge.official" target="_blank" rel="noopener"
                   class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-black hover:text-white transition" title="TikTok">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.48V13a8.2 8.2 0 005.58 2.18V11.7a4.83 4.83 0 01-3.77-1.78V6.69h3.77z"/></svg>
                </a>
                {{-- YouTube --}}
                <a href="https://www.youtube.com/@gn.ge.official" target="_blank" rel="noopener"
                   class="w-11 h-11 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:bg-red-600 hover:text-white transition" title="YouTube">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
            </div>
        </div>

    </div>
</div>
