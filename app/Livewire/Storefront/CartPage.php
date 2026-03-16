<?php

namespace App\Livewire\Storefront;

use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;

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

        $collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();
        $categories = $collectionGroup
            ? LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->whereIsRoot()->with(['urls.language'])->get()
            : collect();

        return view('livewire.storefront.cart-page', [
            'cart' => $cart,
            'lines' => $lines,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
