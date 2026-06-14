<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'supplier_id',
    'purchase_order_number',
    'status',
    'currency',
    'subtotal',
    'tax_total',
    'shipping_total',
    'total',
    'expected_at',
    'received_at',
    'notes',
])]
class PurchaseOrder extends Model
{
    public const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'ordered' => 'Ordered',
        'partially_received' => 'Partially received',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'expected_at' => 'datetime',
            'received_at' => 'datetime',
            'subtotal' => 'integer',
            'tax_total' => 'integer',
            'shipping_total' => 'integer',
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $purchaseOrder): void {
            if (blank($purchaseOrder->purchase_order_number)) {
                $purchaseOrder->purchase_order_number = static::makePurchaseOrderNumber();
            }
        });

        static::saving(function (PurchaseOrder $purchaseOrder): void {
            $purchaseOrder->total = max(0, $purchaseOrder->subtotal + $purchaseOrder->tax_total + $purchaseOrder->shipping_total);
        });
    }

    public static function makePurchaseOrderNumber(): string
    {
        do {
            $number = 'PO-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (static::query()->where('purchase_order_number', $number)->exists());

        return $number;
    }

    public function refreshTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal + $this->tax_total + $this->shipping_total),
        ])->save();
    }
}
