<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'financial_transaction_id',
    'provider',
    'status',
    'amount',
    'currency',
    'transaction_reference',
    'authorization_code',
    'gateway_payload',
    'attempted_at',
    'paid_at',
    'failure_reason',
])]
class PaymentTransaction extends Model
{
    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'authorized' => 'Authorized',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
        'cancelled' => 'Cancelled',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function financialTransaction(): BelongsTo
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'gateway_payload' => 'array',
            'attempted_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }
}
