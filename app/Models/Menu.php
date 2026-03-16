<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['handle', 'name'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('position');
    }

    public static function getByHandle(string $handle): ?self
    {
        return static::with(['items' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->where('handle', $handle)->first();
    }
}
