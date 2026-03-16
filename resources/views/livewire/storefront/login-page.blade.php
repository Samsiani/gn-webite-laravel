@php $locale = app()->getLocale(); $prefix = $locale === 'ka' ? '' : '/' . $locale; @endphp
<div>
    <div class="max-w-md mx-auto px-4 py-12">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Sign In') }}</h1>
            <p class="text-sm text-gray-400">{{ __('Access your account, orders and addresses') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-6">
            <form wire:submit.prevent="login" class="space-y-4">
                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Email') }} @error('email')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                    <input type="email" wire:model="email" name="email" autocomplete="email"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary @error('email') !border-red-300 @enderror"
                           placeholder="email@example.com">
                </div>

                <div>
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-600 mb-1.5">{{ __('Password') }} @error('password')<span class="text-red-400 text-xs font-normal">— {{ $message }}</span>@enderror</label>
                    <input type="password" wire:model="password" name="password" autocomplete="current-password"
                           class="w-full rounded-xl text-sm py-3 focus:border-primary @error('password') !border-red-300 @enderror"
                           placeholder="{{ __('Your password') }}">
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="remember" class="rounded text-primary focus:ring-primary">
                    <span class="text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>

                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl hover:bg-primary-dark transition inline-flex items-center justify-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="login">{{ __('Sign In') }}</span>
                    <span wire:loading wire:target="login">{{ __('Signing in...') }}</span>
                    <svg wire:loading wire:target="login" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-gray-500 mt-5">
            {{ __("Don't have an account?") }}
            <a wire:navigate href="{{ $prefix }}/register" class="text-primary font-medium hover:text-primary-dark transition">{{ __('Create one') }}</a>
        </p>

    </div>
</div>
