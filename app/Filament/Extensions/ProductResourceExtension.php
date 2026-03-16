<?php

namespace App\Filament\Extensions;

use App\Filament\Resources\ProductResource\Pages\EditProductSinglePage;
use Lunar\Admin\Filament\Resources\ProductResource\Pages;
use Lunar\Admin\Support\Extending\ResourceExtension;

class ProductResourceExtension extends ResourceExtension
{
    public function extendPages(array $pages): array
    {
        // Replace the edit page with our single-page editor
        // Keep the same key 'edit' so the route name stays filament.lunar.resources.products.edit
        $pages['edit'] = EditProductSinglePage::route('/{record}/edit');

        // Remove all other sub-pages (keep only index + edit)
        return [
            'index' => $pages['index'],
            'edit' => $pages['edit'],
        ];
    }

    public function extendSubNavigation(array $items): array
    {
        return [];
    }
}
