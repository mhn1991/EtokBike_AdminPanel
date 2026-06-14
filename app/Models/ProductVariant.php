<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'name',
    'sku',
    'options',
    'price_value',
    'stock_quantity',
    'minimum_stock',
    'image_url',
    'is_active',
    'sort_order',
])]
class ProductVariant extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price_value' => 'integer',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
