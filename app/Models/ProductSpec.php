<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpec extends Model
{
    protected $fillable = ['product_id', 'spec_attribute_id', 'value', 'value_en', 'value_ru', 'position'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SpecAttribute::class, 'spec_attribute_id');
    }
}
