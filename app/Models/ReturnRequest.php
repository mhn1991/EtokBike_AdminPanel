<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'order_id',
    'return_number',
    'customer_name',
    'customer_email',
    'customer_phone',
    'status',
    'refund_status',
    'refund_total',
    'reason',
    'requested_at',
    'received_at',
    'notes',
])]
class ReturnRequest extends Model
{
    public const STATUS_OPTIONS = [
        'requested' => 'Requested',
        'approved' => 'Approved',
        'received' => 'Received',
        'refunded' => 'Refunded',
        'rejected' => 'Rejected',
    ];

    public const REFUND_STATUS_OPTIONS = [
        'none' => 'No refund',
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'received_at' => 'datetime',
            'refund_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ReturnRequest $return): void {
            if (blank($return->return_number)) {
                $return->return_number = static::makeReturnNumber();
            }

            $return->requested_at ??= now();
        });
    }

    public static function makeReturnNumber(): string
    {
        do {
            $number = 'RET-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (static::query()->where('return_number', $number)->exists());

        return $number;
    }

    public function refreshTotals(): void
    {
        $this->forceFill([
            'refund_total' => $this->items()->sum('line_total'),
        ])->save();
    }
}
