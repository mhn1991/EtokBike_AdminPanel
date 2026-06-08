<?php

namespace App\Support\Storefront;

use App\Models\Product;
use Illuminate\Support\Collection;

class StorefrontCart
{
    private const SESSION_KEY = 'storefront_cart';

    /**
     * @return array<int, int>
     */
    public function contents(): array
    {
        return collect(session(self::SESSION_KEY, []))
            ->mapWithKeys(fn (mixed $quantity, mixed $productId): array => [(int) $productId => max(1, (int) $quantity)])
            ->all();
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $contents = $this->contents();
        $contents[$product->id] = ($contents[$product->id] ?? 0) + max(1, $quantity);

        $this->put($contents);
    }

    public function update(Product $product, int $quantity): void
    {
        $contents = $this->contents();
        $contents[$product->id] = max(1, $quantity);

        $this->put($contents);
    }

    public function remove(Product $product): void
    {
        $contents = $this->contents();
        unset($contents[$product->id]);

        $this->put($contents);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum($this->contents());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return Collection<int, array{product: Product, quantity: int, line_total: int}>
     */
    public function lines(): Collection
    {
        $contents = $this->contents();

        if ($contents === []) {
            return collect();
        }

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereIn('id', array_keys($contents))
            ->get()
            ->keyBy('id');

        return collect($contents)
            ->map(function (int $quantity, int $productId) use ($products): ?array {
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'line_total' => $quantity * $product->price_value,
                ];
            })
            ->filter()
            ->values();
    }

    public function subtotal(): int
    {
        return $this->lines()->sum('line_total');
    }

    /**
     * @param  array<int, int>  $contents
     */
    private function put(array $contents): void
    {
        session()->put(self::SESSION_KEY, collect($contents)
            ->filter(fn (int $quantity): bool => $quantity > 0)
            ->mapWithKeys(fn (int $quantity, int $productId): array => [$productId => $quantity])
            ->all());
    }
}
