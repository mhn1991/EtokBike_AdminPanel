<?php

namespace Tests\Feature;

use App\Models\Order;
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
}
