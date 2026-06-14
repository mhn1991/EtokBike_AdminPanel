<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'contact_name',
    'email',
    'phone',
    'tax_number',
    'payment_terms',
    'status',
    'address',
    'notes',
])]
class Supplier extends Model
{
    public const STATUS_OPTIONS = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'blocked' => 'Blocked',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
