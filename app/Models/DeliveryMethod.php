<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'subtitle',
    'description',
    'price_label',
    'sort_order',
    'is_active',
])]
class DeliveryMethod extends Model
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function toMobilePayload(): array
    {
        return [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'price' => $this->price_label,
        ];
    }
}
