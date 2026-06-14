<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'code',
    'name',
    'type',
    'value',
    'minimum_order_total',
    'usage_limit',
    'used_count',
    'starts_at',
    'ends_at',
    'is_active',
    'notes',
])]
class DiscountCode extends Model
{
    public const TYPE_OPTIONS = [
        'fixed' => 'Fixed amount',
        'percent' => 'Percentage',
        'free_delivery' => 'Free delivery',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'minimum_order_total' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
