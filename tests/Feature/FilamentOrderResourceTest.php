<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_orders_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->create([
            'customer_name' => 'Panel Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $this->actingAs($user)
            ->get('/admin/orders')
            ->assertOk()
            ->assertSee('Panel Customer');

        $this->actingAs($user)
            ->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Panel Customer');
    }
}
