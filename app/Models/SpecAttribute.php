<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecAttribute extends Model
{
    protected $fillable = ['name', 'name_en', 'name_ru', 'position'];

    public function values(): HasMany
    {
        return $this->hasMany(SpecAttributeValue::class);
    }

    public function productSpecs(): HasMany
    {
        return $this->hasMany(ProductSpec::class);
    }
}
