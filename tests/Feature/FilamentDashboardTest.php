<?php

namespace Tests\Feature;

use App\Models\CustomerMessage;
use App\Models\MessageDepartment;
use App\Models\MobileAnalyticsEvent;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_shows_operational_widgets(): void
    {
        $user = User::factory()->create();

        Order::query()->create([
            'customer_name' => 'Dashboard Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
            'total' => 1250000,
        ]);

        ServiceBooking::query()->create([
            'customer_name' => 'Dashboard Service Customer',
            'service_type' => 'Tune up',
            'status' => 'pending',
        ]);

        $department = MessageDepartment::query()->create([
            'slug' => 'support',
            'title' => 'Support',
            'thread_title' => 'Support thread',
            'composer_title' => 'Message support',
        ]);

        CustomerMessage::query()->create([
            'message_department_id' => $department->id,
            'sender' => 'client',
            'label' => 'Support',
            'text' => 'Dashboard message',
            'is_unread' => true,
        ]);

        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'dashboard-bike',
            'title' => 'Dashboard Bike',
            'subtitle' => 'Demo item',
            'availability' => 'in_stock',
            'price_value' => 1000000,
        ]);

        MobileAnalyticsEvent::query()->create([
            'device_id' => 'dashboard-device',
            'session_id' => 'dashboard-session',
            'event_name' => 'screen_view',
            'screen_id' => 'home',
            'platform' => 'android',
            'occurred_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Mobile app activity')
            ->assertSee('Active users')
            ->assertSee('Mobile usage trend')
            ->assertSee('Operations snapshot')
            ->assertSee('Daily operations')
            ->assertSee('Order status mix')
            ->assertSee('Recent orders')
            ->assertSee('Dashboard Customer');
    }
}
