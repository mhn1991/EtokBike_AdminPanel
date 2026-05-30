<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'label', 'title', 'sort_order', 'is_active'])]
class ServiceCategory extends Model
{
    public function offerings(): HasMany
    {
        return $this->hasMany(ServiceOffering::class);
    }

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }
}
