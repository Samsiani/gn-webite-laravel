<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class AddToCartIcon extends Component
{
    public int $variantId;
    public bool $added = false;

    public function add(): void
    {
        $variant = ProductVariant::findOrFail($this->variantId);
        $cart = CartSession::current(calculate: false);

        if (! $cart) {
            $cart = CartSession::manager();
        }

        $cart->add($variant, 1);
        $this->added = true;
        $this->dispatch('cart-updated');
        $this->js('setTimeout(() => $wire.set("added", false), 1500)');
    }

    public function render()
    {
        return view('livewire.storefront.add-to-cart-icon');
    }
}
