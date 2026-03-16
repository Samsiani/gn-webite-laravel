<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecAttributeValue extends Model
{
    protected $fillable = ['spec_attribute_id', 'value'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SpecAttribute::class, 'spec_attribute_id');
    }
}
