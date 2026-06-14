<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'order_id',
    'return_request_id',
    'receipt_number',
    'type',
    'status',
    'currency',
    'customer_name',
    'customer_email',
    'customer_phone',
    'billing_address',
    'payment_method',
    'payment_status',
    'subtotal',
    'discount_total',
    'delivery_total',
    'tax_total',
    'total',
    'issued_at',
    'cancelled_at',
    'pdf_path',
    'notes',
    'metadata',
])]
class Receipt extends Model
{
    public const TYPE_OPTIONS = [
        'receipt' => 'Receipt',
        'invoice' => 'Invoice',
        'credit_note' => 'Credit note',
    ];

    public const STATUS_OPTIONS = [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'cancelled' => 'Cancelled',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function returnRequest(): BelongsTo
    {
        return $this->belongsTo(ReturnRequest::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptItem::class);
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'delivery_total' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'issued_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Receipt $receipt): void {
            if (blank($receipt->receipt_number)) {
                $receipt->receipt_number = static::makeReceiptNumber($receipt->type ?: 'receipt');
            }

            if ($receipt->status === 'issued') {
                $receipt->issued_at ??= now();
            }
        });

        static::saving(function (Receipt $receipt): void {
            $receipt->total = max(0, $receipt->subtotal - $receipt->discount_total + $receipt->delivery_total + $receipt->tax_total);

            if ($receipt->isDirty('status') && $receipt->status === 'cancelled') {
                $receipt->cancelled_at ??= now();
            }
        });
    }

    public static function makeReceiptNumber(string $type): string
    {
        $prefix = match ($type) {
            'invoice' => 'INV',
            'credit_note' => 'CRN',
            default => 'RCP',
        };

        do {
            $number = $prefix.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (static::query()->where('receipt_number', $number)->exists());

        return $number;
    }

    public function refreshTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - $this->discount_total + $this->delivery_total + $this->tax_total),
        ])->save();
    }
}
