<?php

namespace App\Livewire\Storefront;

use App\Services\StorefrontData;
use Livewire\Component;
use Lunar\Facades\CartSession;

class CartPage extends Component
{
    public function updateQuantity(int $lineId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeItem($lineId);
            return;
        }

        $cart = CartSession::current(calculate: false);
        if ($cart) {
            $cart->updateLine($lineId, $quantity);
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem(int $lineId): void
    {
        $cart = CartSession::current(calculate: false);
        if ($cart) {
            $cart->remove($lineId);
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        $cart = CartSession::current(calculate: true);
        $lines = $cart ? $cart->lines->load('purchasable.product.urls.language', 'purchasable.product.media', 'purchasable.prices') : collect();

        $categories = StorefrontData::categories();

        return view('livewire.storefront.cart-page', [
            'cart' => $cart,
            'lines' => $lines,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
