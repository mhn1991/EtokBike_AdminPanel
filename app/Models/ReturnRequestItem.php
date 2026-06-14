<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'return_request_id',
    'order_item_id',
    'product_id',
    'title',
    'quantity',
    'unit_price',
    'line_total',
    'condition',
    'should_restock',
    'restocked_at',
    'notes',
])]
class ReturnRequestItem extends Model
{
    public const CONDITION_OPTIONS = [
        'sellable' => 'Sellable',
        'inspection' => 'Needs inspection',
        'damaged' => 'Damaged',
    ];

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
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
            'should_restock' => 'boolean',
            'restocked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ReturnRequestItem $item): void {
            $item->line_total = $item->quantity * $item->unit_price;
        });

        static::saved(fn (ReturnRequestItem $item) => $item->returnRequest?->refreshTotals());
        static::deleted(fn (ReturnRequestItem $item) => $item->returnRequest?->refreshTotals());
    }
}
