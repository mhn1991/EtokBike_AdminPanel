<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'order_id',
    'order_item_id',
    'user_id',
    'type',
    'quantity_delta',
    'previous_quantity',
    'new_quantity',
    'reason',
    'reference',
    'metadata',
])]
class StockMovement extends Model
{
    public const TYPE_OPTIONS = [
        'stock_in' => 'Stock in',
        'manual_removal' => 'Manual removal',
        'sale' => 'Sale',
        'sale_return' => 'Sale return',
        'return' => 'Customer return',
        'damage' => 'Damaged',
        'adjustment' => 'Adjustment',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'quantity_delta' => 'integer',
            'previous_quantity' => 'integer',
            'new_quantity' => 'integer',
            'metadata' => 'array',
        ];
    }
}
