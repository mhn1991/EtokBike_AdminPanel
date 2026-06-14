<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'delivery_zone_id',
    'status',
    'carrier_name',
    'tracking_number',
    'shipping_cost',
    'shipped_at',
    'delivered_at',
    'delivery_address',
    'notes',
])]
class Shipment extends Model
{
    public const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'failed' => 'Failed',
        'returned' => 'Returned',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    protected function casts(): array
    {
        return [
            'shipping_cost' => 'integer',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
