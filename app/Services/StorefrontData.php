<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;

class StorefrontData
{
    /**
     * Root product categories — cached 1 hour.
     * Used by layout footer, navigation, shop sidebar, etc.
     */
    public static function categories()
    {
        return Cache::remember('storefront:root_categories', 3600, function () {
            $group = CollectionGroup::where('handle', 'product-categories')->first();
            return $group
                ? LunarCollection::where('collection_group_id', $group->id)
                    ->whereIsRoot()
                    ->with(['urls.language'])
                    ->get()
                : collect();
        });
    }

    /**
     * Categories with product counts — cached 1 hour.
     * Used by shop/category sidebar filters.
     */
    public static function categoriesWithCounts()
    {
        return Cache::remember('storefront:categories_with_counts', 3600, function () {
            $group = CollectionGroup::where('handle', 'product-categories')->first();
            return $group
                ? LunarCollection::where('collection_group_id', $group->id)
                    ->whereIsRoot()
                    ->with(['urls.language'])
                    ->withCount('products')
                    ->get()
                : collect();
        });
    }

    /**
     * Georgia country record — cached forever (static data).
     */
    public static function countryGE()
    {
        return Cache::rememberForever('storefront:country_ge', function () {
            return Country::where('iso2', 'GE')->first();
        });
    }
}
