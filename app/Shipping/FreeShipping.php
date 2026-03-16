<?php

namespace App\Shipping;

use Closure;
use Lunar\Base\ShippingModifier;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Contracts\Cart;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;

class FreeShipping extends ShippingModifier
{
    public function handle(Cart $cart, Closure $next)
    {
        $currency = $cart->currency ?: Currency::where('default', true)->first();
        $taxClass = TaxClass::where('default', true)->first();

        ShippingManifest::addOption(
            new ShippingOption(
                name: __('Free Delivery'),
                description: __('Free delivery within Georgia (4-5 business days)'),
                identifier: 'free-delivery',
                price: new Price(0, $currency, 1),
                taxClass: $taxClass,
            )
        );

        return $next($cart);
    }
}
