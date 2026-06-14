<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_orders_deduct_stock_and_cancelled_orders_restore_it(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5]);
        $order = Order::query()->create([
            'customer_name' => 'Warehouse Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $order->items()->create([
            'product_id' => $product->slug,
            'title' => $product->title,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => $product->price_value,
        ]);

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_movements', 0);

        $order->update(['status' => 'confirmed']);

        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertNotNull($order->fresh()->stock_deducted_at);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'sale',
            'quantity_delta' => -2,
            'previous_quantity' => 5,
            'new_quantity' => 3,
        ]);

        $order->update(['status' => 'cancelled']);

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertNull($order->fresh()->stock_deducted_at);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'sale_return',
            'quantity_delta' => 2,
            'previous_quantity' => 3,
            'new_quantity' => 5,
        ]);
    }

    public function test_deducted_order_item_changes_adjust_only_the_difference(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5]);
        $order = Order::query()->create([
            'customer_name' => 'Warehouse Customer',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $item = $order->items()->create([
            'product_id' => $product->slug,
            'title' => $product->title,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => $product->price_value,
        ]);

        $this->assertSame(3, $product->fresh()->stock_quantity);

        $item->update(['quantity' => 4]);

        $this->assertSame(1, $product->fresh()->stock_quantity);

        $item->update(['quantity' => 1]);

        $this->assertSame(4, $product->fresh()->stock_quantity);

        $item->delete();

        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_deducted_order_item_product_changes_restore_old_product_and_deduct_new_product(): void
    {
        $oldProduct = $this->createProduct([
            'slug' => 'old-bike',
            'sku' => 'OLD-BIKE',
            'stock_quantity' => 5,
        ]);
        $newProduct = $this->createProduct([
            'slug' => 'new-bike',
            'sku' => 'NEW-BIKE',
            'stock_quantity' => 5,
        ]);
        $order = Order::query()->create([
            'customer_name' => 'Warehouse Customer',
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $item = $order->items()->create([
            'product_id' => $oldProduct->slug,
            'title' => $oldProduct->title,
            'sku' => $oldProduct->sku,
            'quantity' => 2,
            'unit_price' => $oldProduct->price_value,
        ]);

        $this->assertSame(3, $oldProduct->fresh()->stock_quantity);
        $this->assertSame(5, $newProduct->fresh()->stock_quantity);

        $item->update([
            'product_id' => $newProduct->slug,
            'title' => $newProduct->title,
            'sku' => $newProduct->sku,
        ]);

        $this->assertSame(5, $oldProduct->fresh()->stock_quantity);
        $this->assertSame(3, $newProduct->fresh()->stock_quantity);
    }

    public function test_known_product_api_orders_validate_available_stock(): void
    {
        $product = $this->createProduct(['stock_quantity' => 1]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Mobile Customer',
            'items' => [
                [
                    'product_id' => $product->slug,
                    'title' => $product->title,
                    'quantity' => 2,
                    'unit_price' => $product->price_value,
                ],
            ],
        ])->assertUnprocessable();
    }

    public function test_stock_movements_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(['stock_quantity' => 2]);

        StockMovement::query()->create([
            'product_id' => $product->id,
            'type' => 'stock_in',
            'quantity_delta' => 2,
            'previous_quantity' => 0,
            'new_quantity' => 2,
            'reason' => 'Initial stock',
        ]);

        $this->actingAs($user)
            ->get('/admin/stock-movements')
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('Initial stock');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes = []): Product
    {
        $category = ProductCategory::query()->firstOrCreate([
            'slug' => 'bikes',
        ], [
            'label' => 'Bikes',
        ]);

        return Product::query()->create(array_merge([
            'product_category_id' => $category->id,
            'slug' => 'bike-test',
            'sku' => 'BIKE-TEST',
            'title' => 'Test Bike',
            'subtitle' => 'Warehouse test',
            'availability' => 'in_stock',
            'price_value' => 1000,
            'stock_quantity' => 0,
            'minimum_stock' => 1,
        ], $attributes));
    }
}
