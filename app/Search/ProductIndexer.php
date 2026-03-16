<?php

namespace App\Search;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lunar\Search\ProductIndexer as LunarProductIndexer;

class ProductIndexer extends LunarProductIndexer
{
    public function getSortableFields(): array
    {
        return [
            'created_at',
            'updated_at',
            'skus',
            'status',
            'price',
            'name_ka',
            'name_en',
            'name_ru',
        ];
    }

    public function getFilterableFields(): array
    {
        return [
            '__soft_deleted',
            'skus',
            'status',
            'brand',
            'product_type',
            'collection_ids',
            'price',
        ];
    }

    public function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with([
            'thumbnail',
            'variants.prices',
            'productType',
            'brand',
            'collections',
        ]);
    }

    public function toSearchableArray(Model $model): array
    {
        $data = parent::toSearchableArray($model);

        // Add lowest price in tetri for filtering/sorting
        $minPrice = $model->variants
            ->flatMap(fn ($v) => $v->prices)
            ->min('price.value');
        $data['price'] = $minPrice ?? 0;

        // Add collection IDs for category filtering
        $data['collection_ids'] = $model->collections->pluck('id')->map(fn ($id) => (int) $id)->toArray();

        return $data;
    }
}
