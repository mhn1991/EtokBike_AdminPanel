<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Inventory\InventoryManager;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $product = Product::query()->findOrFail($data['product_id']);
        $quantity = (int) $data['quantity'];
        $type = (string) $data['movement_type'];
        $direction = $this->directionFor($type, (string) ($data['adjustment_direction'] ?? 'in'));

        return app(InventoryManager::class)->adjust(
            product: $product,
            quantityDelta: $quantity * $direction,
            type: $type,
            reason: $data['reason'] ?? null,
            userId: auth()->id(),
            reference: $data['reference'] ?? null,
        ) ?? new StockMovement();
    }

    private function directionFor(string $type, string $adjustmentDirection): int
    {
        if (in_array($type, ['manual_removal', 'damage'], true)) {
            return -1;
        }

        if ($type === 'adjustment' && $adjustmentDirection === 'out') {
            return -1;
        }

        return 1;
    }
}
