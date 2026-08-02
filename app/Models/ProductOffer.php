<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'type',
    'title',
    'badge_text',
    'sale_price_value',
    'buy_quantity',
    'get_quantity',
    'get_discount_percent',
    'discount_type',
    'discount_value',
    'minimum_quantity',
    'starts_at',
    'ends_at',
    'is_active',
    'sort_order',
])]
class ProductOffer extends Model
{
    public const TYPE_OPTIONS = [
        'sale_price' => 'Time-limited sale price',
        'buy_x_get_y' => 'Buy X get Y',
        'auto_discount' => 'Automatic discount',
    ];

    public const DISCOUNT_TYPE_OPTIONS = [
        'percent' => 'Percentage',
        'fixed' => 'Fixed amount',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'sale_price_value' => 'integer',
            'buy_quantity' => 'integer',
            'get_quantity' => 'integer',
            'get_discount_percent' => 'integer',
            'discount_value' => 'integer',
            'minimum_quantity' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
