<?php

namespace App\Models;

use App\Support\Mobile\ImageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable([
    'product_id',
    'name',
    'sku',
    'options',
    'price_value',
    'stock_quantity',
    'minimum_stock',
    'image_url',
    'is_active',
    'sort_order',
])]
class ProductVariant extends Model
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'price_value' => 'integer',
            'stock_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function effectivePriceValue(?Product $product = null): int
    {
        return $this->price_value ?? $product?->price_value ?? $this->product?->price_value ?? 0;
    }

    public function optionSummary(): string
    {
        return $this->flatOptions()
            ->map(fn (string $value, string $key): string => "{$key}: {$value}")
            ->join(' · ');
    }

    /**
     * @return array<string, mixed>
     */
    public function toMobilePayload(Product $product): array
    {
        $priceValue = $this->effectivePriceValue($product);

        return [
            'id' => (string) $this->getKey(),
            'name' => $this->name,
            'sku' => $this->sku,
            'options' => $this->options ?? [],
            'optionSummary' => $this->optionSummary(),
            'priceValue' => $priceValue,
            'price' => number_format($priceValue).' تومان',
            'stockQuantity' => $this->stock_quantity,
            'minimumStock' => $this->minimum_stock,
            'imageUrl' => ImageUrl::resolveForMobile($this->image_url ?: $product->image_url),
            'sortOrder' => $this->sort_order,
            'isActive' => $this->is_active,
        ];
    }

    /**
     * @return Collection<string, string>
     */
    private function flatOptions(): Collection
    {
        $options = collect($this->options ?? []);

        $primaryOptions = collect([
            'Color' => $options->get('color'),
            'Size' => $options->get('size'),
        ]);

        $extraOptions = collect($options->get('attributes', []))
            ->filter(fn (mixed $value): bool => ! is_array($value));

        if ($extraOptions->isEmpty()) {
            $extraOptions = $options
                ->except(['color', 'size', 'attributes'])
                ->filter(fn (mixed $value): bool => ! is_array($value));
        }

        return $primaryOptions
            ->merge($extraOptions)
            ->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): string => (string) $value);
    }
}
