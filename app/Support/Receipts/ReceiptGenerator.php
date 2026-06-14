<?php

namespace App\Support\Receipts;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Illuminate\Support\Facades\DB;

class ReceiptGenerator
{
    public function forOrder(Order $order, string $type = 'receipt', string $status = 'issued'): Receipt
    {
        $existingReceipt = Receipt::query()
            ->where('order_id', $order->id)
            ->where('type', $type)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingReceipt) {
            return $existingReceipt->load('items');
        }

        return DB::transaction(function () use ($order, $type, $status): Receipt {
            $order = $order->fresh('items') ?? $order->loadMissing('items');

            $receipt = Receipt::query()->create([
                'order_id' => $order->id,
                'type' => $type,
                'status' => $status,
                'currency' => $order->currency,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'discount_total' => $order->discount_total,
                'delivery_total' => $order->delivery_total,
                'tax_total' => 0,
                'notes' => $order->customer_notes,
                'metadata' => [
                    'source' => 'order',
                    'order_number' => $order->order_number,
                    'fulfillment_method' => $order->fulfillment_method,
                ],
            ]);

            foreach ($order->items as $item) {
                $product = $this->productForOrderItem($item);

                $receipt->items()->create([
                    'order_item_id' => $item->id,
                    'product_id' => $product?->id,
                    'title' => $item->title,
                    'sku' => $item->sku ?: $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'metadata' => $item->metadata,
                ]);
            }

            return $receipt->fresh(['items']);
        });
    }

    public function creditNoteForReturn(ReturnRequest $returnRequest, string $status = 'issued'): Receipt
    {
        $existingReceipt = Receipt::query()
            ->where('return_request_id', $returnRequest->id)
            ->where('type', 'credit_note')
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingReceipt) {
            return $existingReceipt->load('items');
        }

        return DB::transaction(function () use ($returnRequest, $status): Receipt {
            $returnRequest = $returnRequest->fresh(['items.product', 'order']) ?? $returnRequest->loadMissing(['items.product', 'order']);

            $receipt = Receipt::query()->create([
                'order_id' => $returnRequest->order_id,
                'return_request_id' => $returnRequest->id,
                'type' => 'credit_note',
                'status' => $status,
                'currency' => $returnRequest->order?->currency ?? 'IRR',
                'customer_name' => $returnRequest->customer_name,
                'customer_email' => $returnRequest->customer_email,
                'customer_phone' => $returnRequest->customer_phone,
                'payment_status' => $returnRequest->refund_status,
                'subtotal' => $returnRequest->refund_total,
                'discount_total' => 0,
                'delivery_total' => 0,
                'tax_total' => 0,
                'notes' => $returnRequest->notes,
                'metadata' => [
                    'source' => 'return_request',
                    'return_number' => $returnRequest->return_number,
                    'order_number' => $returnRequest->order?->order_number,
                    'reason' => $returnRequest->reason,
                ],
            ]);

            foreach ($returnRequest->items as $item) {
                $receipt->items()->create([
                    'return_request_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'title' => $item->title,
                    'sku' => $item->product?->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'metadata' => [
                        'condition' => $item->condition,
                        'should_restock' => $item->should_restock,
                    ],
                ]);
            }

            return $receipt->fresh(['items']);
        });
    }

    private function productForOrderItem(OrderItem $item): ?Product
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
}
