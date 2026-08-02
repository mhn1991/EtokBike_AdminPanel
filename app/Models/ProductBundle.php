<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type',
    'title',
    'slug',
    'description',
    'image_url',
    'bundle_price_value',
    'is_active',
    'starts_at',
    'ends_at',
    'sort_order',
])]
class ProductBundle extends Model
{
    public const TYPE_OPTIONS = [
        'fixed_price' => 'Fixed-price bundle',
        'cross_sell' => 'Frequently bought together',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleItem::class);
    }

    protected function casts(): array
    {
        return [
            'bundle_price_value' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }
}
