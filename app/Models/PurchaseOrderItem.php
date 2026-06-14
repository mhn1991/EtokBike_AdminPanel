<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'purchase_order_id',
    'product_id',
    'product_unit_id',
    'description',
    'sku',
    'quantity',
    'unit_cost',
    'line_total',
    'received_quantity',
])]
class PurchaseOrderItem extends Model
{
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'integer',
            'line_total' => 'integer',
            'received_quantity' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $item): void {
            $item->line_total = $item->quantity * $item->unit_cost;
        });

        static::saved(fn (PurchaseOrderItem $item) => $item->purchaseOrder?->refreshTotals());
        static::deleted(fn (PurchaseOrderItem $item) => $item->purchaseOrder?->refreshTotals());
    }
}
