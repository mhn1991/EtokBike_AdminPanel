<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_category_id',
    'slug',
    'title',
    'subtitle',
    'description',
    'availability',
    'price_value',
    'price_label',
    'stock_label',
    'thumbnail_text',
    'thumbnail_color',
    'image_url',
    'sort_order',
    'is_featured',
    'is_active',
])]
class Product extends Model
{
    public const AVAILABILITY_OPTIONS = [
        'in_stock' => 'In stock',
        'low_stock' => 'Low stock',
        'orderable' => 'Orderable',
        'out_of_stock' => 'Out of stock',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    protected function casts(): array
    {
        return [
            'price_value' => 'integer',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(): array
    {
        return [
            'id' => $this->slug,
            'category' => $this->category?->slug,
            'availability' => $this->availability,
            'priceValue' => $this->price_value,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'price' => $this->price_label ?: number_format($this->price_value).' تومان',
            'stockLabel' => $this->stock_label,
            'thumbnailText' => $this->thumbnail_text,
            'thumbnailColor' => $this->thumbnail_color,
            'imageUrl' => $this->image_url,
        ];
    }
}
