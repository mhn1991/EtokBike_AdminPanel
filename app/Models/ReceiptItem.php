<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'receipt_id',
    'order_item_id',
    'return_request_item_id',
    'product_id',
    'title',
    'sku',
    'quantity',
    'unit_price',
    'line_total',
    'metadata',
])]
class ReceiptItem extends Model
{
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function returnRequestItem(): BelongsTo
    {
        return $this->belongsTo(ReturnRequestItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
        static::saving(function (ReceiptItem $item): void {
            $item->line_total = $item->quantity * $item->unit_price;
        });

        static::saved(fn (ReceiptItem $item) => $item->receipt?->refreshTotals());
        static::deleted(fn (ReceiptItem $item) => $item->receipt?->refreshTotals());
    }
}
