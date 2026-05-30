<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;

#[Fillable([
    'order_id',
    'product_id',
    'title',
    'sku',
    'quantity',
    'unit_price',
    'line_total',
    'metadata',
])]
class OrderItem extends Model
{
    public function order(): BelongsToRelation
    {
        return $this->belongsTo(Order::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'line_total' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item): void {
            $item->line_total = $item->quantity * $item->unit_price;
        });

        static::saved(fn (OrderItem $item) => $item->order?->refreshTotals());
        static::deleted(fn (OrderItem $item) => $item->order?->refreshTotals());
    }
}
