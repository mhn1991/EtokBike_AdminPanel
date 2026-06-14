<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'code',
    'fee',
    'minimum_order_total',
    'estimated_days',
    'is_active',
    'notes',
])]
class DeliveryZone extends Model
{
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    protected function casts(): array
    {
        return [
            'fee' => 'integer',
            'minimum_order_total' => 'integer',
            'estimated_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
