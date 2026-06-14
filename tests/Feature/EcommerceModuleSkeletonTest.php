<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\FinancialTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\ReturnRequest;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceModuleSkeletonTest extends TestCase
{
    use RefreshDatabase;

    public function test_ecommerce_module_admin_screens_render(): void
    {
        $user = User::factory()->create();

        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'skeleton-bike',
            'sku' => 'SKELETON-BIKE',
            'title' => 'Skeleton Bike',
            'subtitle' => 'Module product',
            'availability' => 'in_stock',
            'price_value' => 1000000,
            'stock_quantity' => 10,
        ]);

        $unit = ProductUnit::query()->create([
            'product_id' => $product->id,
            'name' => 'Box',
            'abbreviation' => 'box',
            'quantity_in_base_units' => 20,
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'Skeleton Supplier',
            'status' => 'active',
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);

        $order = Order::query()->create([
            'customer_name' => 'Skeleton Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'delivery',
        ]);

        $transaction = FinancialTransaction::query()->create([
            'order_id' => $order->id,
            'type' => 'sale_income',
            'direction' => 'income',
            'status' => 'pending',
            'amount' => 1000000,
            'occurred_at' => now(),
        ]);

        $return = ReturnRequest::query()->create([
            'order_id' => $order->id,
            'customer_name' => 'Skeleton Customer',
            'status' => 'requested',
            'refund_status' => 'pending',
        ]);

        $zone = DeliveryZone::query()->create([
            'name' => 'Central City',
            'code' => 'central',
            'fee' => 50000,
        ]);

        $shipment = Shipment::query()->create([
            'order_id' => $order->id,
            'delivery_zone_id' => $zone->id,
            'status' => 'pending',
            'tracking_number' => 'TRK-SKELETON',
        ]);

        $paths = [
            '/admin/financial-transactions',
            '/admin/financial-transactions/create',
            "/admin/financial-transactions/{$transaction->id}/edit",
            '/admin/suppliers',
            '/admin/suppliers/create',
            "/admin/suppliers/{$supplier->id}/edit",
            '/admin/product-units',
            '/admin/product-units/create',
            "/admin/product-units/{$unit->id}/edit",
            '/admin/purchase-orders',
            '/admin/purchase-orders/create',
            "/admin/purchase-orders/{$purchaseOrder->id}/edit",
            '/admin/return-requests',
            '/admin/return-requests/create',
            "/admin/return-requests/{$return->id}/edit",
            '/admin/delivery-zones',
            '/admin/delivery-zones/create',
            "/admin/delivery-zones/{$zone->id}/edit",
            '/admin/shipments',
            '/admin/shipments/create',
            "/admin/shipments/{$shipment->id}/edit",
            '/admin/commerce-reports',
        ];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk();
        }
    }
}
