<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_id',
    'name',
    'abbreviation',
    'quantity_in_base_units',
    'is_base_unit',
    'sort_order',
])]
class ProductUnit extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    protected function casts(): array
    {
        return [
            'quantity_in_base_units' => 'integer',
            'is_base_unit' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
