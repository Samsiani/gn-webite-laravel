<div class="flex gap-2">
    {{-- Quantity --}}
    <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden shrink-0" style="height:48px">
        <button wire:click="decrement" class="px-3 h-full text-gray-500 hover:bg-gray-50 hover:text-primary transition text-lg font-medium">−</button>
        <input type="number" wire:model="quantity" min="1" max="99"
               class="w-10 h-full text-center border-x border-gray-200 text-sm font-medium focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
        <button wire:click="increment" class="px-3 h-full text-gray-500 hover:bg-gray-50 hover:text-primary transition text-lg font-medium">+</button>
    </div>

    {{-- Add to Cart Button --}}
    <button wire:click="addToCart"
            wire:loading.attr="disabled"
            aria-label="Add to Cart"
            style="height:48px"
            class="flex-1 rounded-xl font-semibold text-sm transition flex items-center justify-center gap-2
                   {{ $added ? 'bg-green-500 text-white' : 'bg-primary text-white hover:bg-primary-dark' }}">
        <span wire:loading.remove wire:target="addToCart">
            @if($added)
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            @endif
        </span>
        <span wire:loading.remove wire:target="addToCart">
            {{ $added ? __('Added!') : __('Add to Cart') }}
        </span>
        <span wire:loading wire:target="addToCart">
            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        </span>
    </button>
</div>
