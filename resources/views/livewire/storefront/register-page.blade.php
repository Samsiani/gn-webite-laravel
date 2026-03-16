@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-md mx-auto px-4 py-12">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Create Account') }}</h1>
            <p class="text-sm text-gray-400">{{ __('Join us to track orders and manage your addresses') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <form wire:submit.prevent="register" class="space-y-4">
                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Full Name') }} @error('name')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                    <input type="text" wire:model.blur="name" name="name" autocomplete="name"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary @error('name') !border-red-300 @enderror"
                           placeholder="{{ __('Your full name') }}">
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Email') }} @error('email')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                    <input type="email" wire:model.blur="email" name="email" autocomplete="email"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary @error('email') !border-red-300 @enderror"
                           placeholder="email@example.com">
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Phone') }}</label>
                    <input type="tel" wire:model="phone" name="phone" autocomplete="tel"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                           placeholder="+995 5XX XX XX XX">
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Password') }} @error('password')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                    <input type="password" wire:model="password" name="password" autocomplete="new-password"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary @error('password') !border-red-300 @enderror"
                           placeholder="{{ __('Min 6 characters') }}">
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Confirm Password') }}</label>
                    <input type="password" wire:model="password_confirmation" name="password_confirmation" autocomplete="new-password"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary"
                           placeholder="{{ __('Repeat password') }}">
                </div>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl hover:bg-primary-dark transition inline-flex items-center justify-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="register">{{ __('Create Account') }}</span>
                    <span wire:loading wire:target="register">{{ __('Creating...') }}</span>
                    <svg wire:loading wire:target="register" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-5">
            {{ __('Already have an account?') }}
            <a wire:navigate href="{{ $prefix }}/login" class="text-primary font-medium hover:text-primary-dark transition">{{ __('Sign In') }}</a>
        </p>

    </div>
</div>
