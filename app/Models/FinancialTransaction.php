<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'purchase_order_id',
    'supplier_id',
    'type',
    'direction',
    'status',
    'amount',
    'currency',
    'occurred_at',
    'payment_method',
    'reference',
    'notes',
])]
class FinancialTransaction extends Model
{
    public const TYPE_OPTIONS = [
        'sale_income' => 'Sale income',
        'refund' => 'Refund',
        'supplier_payment' => 'Supplier payment',
        'expense' => 'Expense',
        'adjustment' => 'Adjustment',
    ];

    public const DIRECTION_OPTIONS = [
        'income' => 'Income',
        'expense' => 'Expense',
    ];

    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'posted' => 'Posted',
        'void' => 'Void',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }
}
