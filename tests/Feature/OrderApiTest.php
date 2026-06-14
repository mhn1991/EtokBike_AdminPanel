<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order_from_the_api(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Mobile Customer',
            'customer_phone' => '+989120000000',
            'fulfillment_method' => 'pickup',
            'items' => [
                [
                    'product_id' => 'bike-etx-200',
                    'title' => 'دوچرخه کوهستان ETX 200',
                    'quantity' => 2,
                    'unit_price' => 28500000,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.payment_status', 'unpaid')
            ->assertJsonPath('data.subtotal', 57000000)
            ->assertJsonPath('data.total', 57000000);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Mobile Customer',
            'total' => 57000000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'title' => 'دوچرخه کوهستان ETX 200',
            'quantity' => 2,
            'line_total' => 57000000,
        ]);

        $this->assertTrue(Order::query()->where('customer_name', 'Mobile Customer')->exists());
    }

    public function test_authenticated_order_api_links_customer_and_delivery_records(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-api-order-test',
            'sku' => 'BIKE-API',
            'title' => 'Bike API Order Test',
            'subtitle' => 'API product',
            'availability' => 'in_stock',
            'price_value' => 5000,
            'stock_quantity' => 10,
        ]);

        $zone = DeliveryZone::query()->create([
            'name' => 'Central',
            'code' => 'central-api',
            'fee' => 1000,
        ]);

        $user = User::factory()->create([
            'name' => 'API Customer',
            'email' => 'api-customer@example.com',
        ]);
        $token = $user->createToken('android')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/orders', [
                'customer_name' => 'API Customer',
                'customer_email' => 'api-customer@example.com',
                'customer_phone' => '+989127777777',
                'fulfillment_method' => 'delivery',
                'delivery_zone_id' => $zone->id,
                'delivery_address' => 'API delivery address',
                'payment_method' => 'cash_on_delivery',
                'items' => [
                    [
                        'product_id' => $product->slug,
                        'title' => $product->title,
                        'quantity' => 1,
                        'unit_price' => 5000,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.total', 6000);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'customer_email' => 'api-customer@example.com',
            'delivery_total' => 1000,
            'total' => 6000,
        ]);

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'delivery_address' => 'API delivery address',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'cash_on_delivery',
        ]);
    }
}
