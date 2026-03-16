<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class AddToCart extends Component
{
    public int $variantId;
    public int $quantity = 1;
    public bool $added = false;

    public function addToCart(): void
    {
        $variant = ProductVariant::findOrFail($this->variantId);

        $cart = CartSession::current(calculate: false);

        if (! $cart) {
            $cart = CartSession::manager();
        }

        $cart->add($variant, $this->quantity);

        $this->added = true;
        $this->dispatch('cart-updated');

        // Reset "added" after 2 seconds
        $this->js('setTimeout(() => $wire.set("added", false), 2000)');
    }

    public function increment(): void
    {
        $this->quantity = min($this->quantity + 1, 99);
    }

    public function decrement(): void
    {
        $this->quantity = max($this->quantity - 1, 1);
    }

    public function render()
    {
        return view('livewire.storefront.add-to-cart');
    }
}
