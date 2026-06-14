<?php

namespace App\Support\Inventory;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryManager
{
    public function adjust(
        Product $product,
        int $quantityDelta,
        string $type,
        ?string $reason = null,
        ?Order $order = null,
        ?OrderItem $orderItem = null,
        ?int $userId = null,
        ?string $reference = null,
        array $metadata = [],
    ): ?StockMovement {
        if ($quantityDelta === 0) {
            return null;
        }

        return DB::transaction(function () use ($product, $quantityDelta, $type, $reason, $order, $orderItem, $userId, $reference, $metadata): StockMovement {
            $lockedProduct = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $previousQuantity = $lockedProduct->stock_quantity;
            $newQuantity = $previousQuantity + $quantityDelta;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'stock' => "Not enough stock for {$lockedProduct->title}. Available: {$previousQuantity}.",
                ]);
            }

            $lockedProduct->forceFill([
                'stock_quantity' => $newQuantity,
                'availability' => $this->availabilityFor($lockedProduct, $newQuantity),
            ])->save();

            return StockMovement::query()->create([
                'product_id' => $lockedProduct->id,
                'order_id' => $order?->id,
                'order_item_id' => $orderItem?->id,
                'user_id' => $userId,
                'type' => $type,
                'quantity_delta' => $quantityDelta,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reason' => $reason,
                'reference' => $reference,
                'metadata' => $metadata ?: null,
            ]);
        });
    }

    public function ensureOrderCanBeDeducted(Order $order): void
    {
        if (! $this->orderShouldDeductStock($order) || filled($order->stock_deducted_at)) {
            return;
        }

        foreach ($order->items()->get() as $item) {
            $product = $this->productForOrderItem($item);

            if (! $product || $product->stock_quantity >= $item->quantity) {
                continue;
            }

            throw ValidationException::withMessages([
                'stock' => "Not enough stock for {$product->title}. Available: {$product->stock_quantity}, requested: {$item->quantity}.",
            ]);
        }
    }

    public function reconcileOrder(Order $order): void
    {
        $order = $order->fresh(['items']);

        if (! $order) {
            return;
        }

        if ($this->orderShouldDeductStock($order) && blank($order->stock_deducted_at)) {
            $this->deductOrder($order);

            return;
        }

        if (! $this->orderShouldDeductStock($order) && filled($order->stock_deducted_at)) {
            $this->restoreOrder($order);
        }
    }

    public function deductOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order->forceFill(['stock_deducted_at' => now()])->saveQuietly();

            foreach ($order->items as $item) {
                $this->syncDeductedOrderItem($item);
            }
        });
    }

    public function restoreOrder(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            foreach ($order->items as $item) {
                $this->restoreDeductedOrderItem($item);
            }

            $order->forceFill(['stock_deducted_at' => null])->saveQuietly();
        });
    }

    public function syncDeductedOrderItem(OrderItem $item): void
    {
        $order = $item->order;

        if (! $order || blank($order->stock_deducted_at)) {
            return;
        }

        $product = $this->productForOrderItem($item);

        if (! $product) {
            return;
        }

        $deductedByProduct = $this->deductedQuantitiesByProductForOrderItem($item);

        foreach ($deductedByProduct as $productId => $deductedQuantity) {
            if ($productId === $product->id || $deductedQuantity <= 0) {
                continue;
            }

            $previousProduct = Product::query()->find($productId);

            if (! $previousProduct) {
                continue;
            }

            $this->adjust(
                product: $previousProduct,
                quantityDelta: $deductedQuantity,
                type: 'sale_return',
                reason: "Order {$order->order_number} item product changed",
                order: $order,
                orderItem: $item,
                userId: auth()->id(),
                reference: $order->order_number,
                metadata: ['title' => $item->title, 'sku' => $item->sku],
            );
        }

        $currentlyDeducted = $deductedByProduct[$product->id] ?? 0;
        $difference = $item->quantity - $currentlyDeducted;

        if ($difference > 0) {
            $this->adjust(
                product: $product,
                quantityDelta: -$difference,
                type: 'sale',
                reason: "Order {$order->order_number}",
                order: $order,
                orderItem: $item,
                userId: auth()->id(),
                reference: $order->order_number,
                metadata: ['title' => $item->title, 'sku' => $item->sku],
            );
        }

        if ($difference < 0) {
            $this->adjust(
                product: $product,
                quantityDelta: abs($difference),
                type: 'sale_return',
                reason: "Order {$order->order_number} item quantity reduced",
                order: $order,
                orderItem: $item,
                userId: auth()->id(),
                reference: $order->order_number,
                metadata: ['title' => $item->title, 'sku' => $item->sku],
            );
        }
    }

    public function restoreDeductedOrderItem(OrderItem $item): void
    {
        $order = $item->order;
        $deductedByProduct = $this->deductedQuantitiesByProductForOrderItem($item);

        if (! $order || $deductedByProduct === []) {
            return;
        }

        foreach ($deductedByProduct as $productId => $quantity) {
            $product = Product::query()->find($productId);

            if (! $product || $quantity <= 0) {
                continue;
            }

            $this->adjust(
                product: $product,
                quantityDelta: $quantity,
                type: 'sale_return',
                reason: "Order {$order->order_number} restored",
                order: $order,
                orderItem: $item,
                userId: auth()->id(),
                reference: $order->order_number,
                metadata: ['title' => $item->title, 'sku' => $item->sku],
            );
        }
    }

    public function productForOrderItem(OrderItem $item): ?Product
    {
        $metadataProductId = $item->metadata['product_database_id'] ?? null;

        if ($metadataProductId) {
            return Product::query()->find($metadataProductId);
        }

        if (filled($item->product_id)) {
            return Product::query()
                ->where('slug', $item->product_id)
                ->orWhere('sku', $item->product_id)
                ->first();
        }

        if (filled($item->sku)) {
            return Product::query()
                ->where('sku', $item->sku)
                ->orWhere('slug', $item->sku)
                ->first();
        }

        return null;
    }

    public function orderShouldDeductStock(Order $order): bool
    {
        if ($order->status === 'cancelled' || in_array($order->payment_status, ['refunded', 'failed'], true)) {
            return false;
        }

        return in_array($order->status, ['confirmed', 'processing', 'ready', 'completed'], true)
            || $order->payment_status === 'paid';
    }

    /**
     * @return array<int, int>
     */
    private function deductedQuantitiesByProductForOrderItem(OrderItem $item): array
    {
        return StockMovement::query()
            ->where('order_item_id', $item->id)
            ->whereIn('type', ['sale', 'sale_return'])
            ->selectRaw('product_id, SUM(quantity_delta) as net_delta')
            ->groupBy('product_id')
            ->get()
            ->mapWithKeys(fn (StockMovement $movement): array => [
                $movement->product_id => max(0, -((int) $movement->net_delta)),
            ])
            ->filter(fn (int $quantity): bool => $quantity > 0)
            ->all();
    }

    private function availabilityFor(Product $product, int $quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }

        if ($product->minimum_stock > 0 && $quantity <= $product->minimum_stock) {
            return 'low_stock';
        }

        return $product->availability === 'orderable' ? 'orderable' : 'in_stock';
    }
}
