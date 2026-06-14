<?php

namespace App\Models;

use App\Support\Mobile\ImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'product_category_id',
    'slug',
    'sku',
    'title',
    'subtitle',
    'description',
    'seo_title',
    'seo_description',
    'canonical_url',
    'robots',
    'og_title',
    'og_description',
    'og_image',
    'include_in_sitemap',
    'sitemap_priority',
    'sitemap_change_frequency',
    'availability',
    'price_value',
    'price_label',
    'stock_label',
    'stock_quantity',
    'reserved_quantity',
    'minimum_stock',
    'warehouse_location',
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

    public const ROBOTS_OPTIONS = [
        'index,follow' => 'Index, follow',
        'noindex,follow' => 'No index, follow',
        'noindex,nofollow' => 'No index, no follow',
    ];

    public const CHANGE_FREQUENCY_OPTIONS = [
        'always' => 'Always',
        'hourly' => 'Hourly',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'never' => 'Never',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    protected function casts(): array
    {
        return [
            'price_value' => 'integer',
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'sort_order' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function availableStock(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    public function hasEnoughStock(int $quantity): bool
    {
        return $this->availability === 'orderable' || $this->availableStock() >= $quantity;
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
            'sku' => $this->sku ?: $this->slug,
            'stockQuantity' => $this->stock_quantity,
            'availableStock' => $this->availableStock(),
            'priceValue' => $this->price_value,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'price' => $this->price_label ?: number_format($this->price_value).' تومان',
            'stockLabel' => $this->stock_label,
            'thumbnailText' => $this->thumbnail_text,
            'thumbnailColor' => $this->thumbnail_color,
            'imageUrl' => ImageUrl::resolve($this->image_url),
        ];
    }
}
