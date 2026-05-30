<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToRelation;
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation;
use Illuminate\Support\Str;

#[Fillable([
    'order_number',
    'user_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'status',
    'payment_status',
    'fulfillment_method',
    'currency',
    'subtotal',
    'discount_total',
    'delivery_total',
    'total',
    'customer_notes',
    'admin_notes',
    'placed_at',
])]
class Order extends Model
{
    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUS_OPTIONS = [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
        'failed' => 'Failed',
    ];

    public const FULFILLMENT_METHOD_OPTIONS = [
        'pickup' => 'Pickup',
        'delivery' => 'Delivery',
    ];

    public function user(): BelongsToRelation
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasManyRelation
    {
        return $this->hasMany(OrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'delivery_total' => 'integer',
            'total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->order_number)) {
                $order->order_number = static::makeOrderNumber();
            }

            $order->placed_at ??= now();
        });

        static::saving(function (Order $order): void {
            $order->total = max(0, $order->subtotal - $order->discount_total + $order->delivery_total);
        });
    }

    public static function makeOrderNumber(): string
    {
        do {
            $orderNumber = 'ETB-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    public function refreshTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - $this->discount_total + $this->delivery_total),
        ])->save();
    }
}
