<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\On;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class CartManager extends Component
{
    public int $cartCount = 0;
    public bool $showMiniCart = false;

    public function mount(): void
    {
        $this->updateCount();
    }

    #[On('cart-updated')]
    public function updateCount(): void
    {
        $cart = CartSession::current(calculate: false);
        $this->cartCount = $cart ? $cart->lines->sum('quantity') : 0;
    }

    public function toggleMiniCart(): void
    {
        $this->showMiniCart = ! $this->showMiniCart;
    }

    public function closeMiniCart(): void
    {
        $this->showMiniCart = false;
    }

    public function render()
    {
        $cart = null;
        $lines = collect();

        // Only calculate full cart when mini cart is open
        if ($this->showMiniCart) {
            $cart = CartSession::current(calculate: true);
            $lines = $cart ? $cart->lines->load('purchasable.product.urls.language', 'purchasable.product.media', 'purchasable.prices') : collect();
        }

        return view('livewire.storefront.cart-manager', [
            'cart' => $cart,
            'lines' => $lines,
        ]);
    }
}
