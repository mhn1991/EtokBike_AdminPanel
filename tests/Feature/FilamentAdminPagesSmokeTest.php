<?php

namespace Tests\Feature;

use App\Models\CustomerMessage;
use App\Models\MessageDepartment;
use App\Models\MobileScreen;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ServiceBooking;
use App\Models\ServiceCategory;
use App\Models\ServiceOffering;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentAdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_page_by_page(): void
    {
        $user = User::factory()->create();

        $order = Order::query()->create([
            'customer_name' => 'Smoke Order Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $productCategory = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $productCategory->id,
            'slug' => 'smoke-bike',
            'title' => 'Smoke Bike',
            'subtitle' => 'Smoke product',
            'availability' => 'in_stock',
            'price_value' => 1000000,
        ]);

        $programCategory = ProgramCategory::query()->create([
            'slug' => 'rides',
            'label' => 'Rides',
            'title' => 'Ride programs',
        ]);

        $program = Program::query()->create([
            'program_category_id' => $programCategory->id,
            'slug' => 'smoke-ride',
            'title' => 'Smoke Ride',
            'subtitle' => 'Smoke program',
            'date_value' => '2026-06-05',
            'date_label' => 'Friday morning',
            'program_state' => 'future',
        ]);

        $serviceCategory = ServiceCategory::query()->create([
            'slug' => 'maintenance',
            'label' => 'Maintenance',
            'title' => 'Maintenance services',
        ]);

        $serviceOffering = ServiceOffering::query()->create([
            'service_category_id' => $serviceCategory->id,
            'slug' => 'smoke-service',
            'title' => 'Smoke Service',
            'subtitle' => 'Smoke service offering',
        ]);

        $serviceBooking = ServiceBooking::query()->create([
            'customer_name' => 'Smoke Booking Customer',
            'service_type' => 'Tune up',
            'status' => 'pending',
        ]);

        $department = MessageDepartment::query()->create([
            'slug' => 'support',
            'title' => 'Support',
            'thread_title' => 'Support thread',
            'composer_title' => 'Message support',
        ]);

        $message = CustomerMessage::query()->create([
            'message_department_id' => $department->id,
            'sender' => 'client',
            'label' => 'Support',
            'text' => 'Smoke message',
            'is_unread' => true,
        ]);

        $screen = MobileScreen::query()->create([
            'screen_id' => 'home',
            'title' => 'Home',
            'version' => 1,
        ]);

        $paths = [
            '/admin',
            '/admin/orders',
            '/admin/orders/create',
            "/admin/orders/{$order->id}",
            "/admin/orders/{$order->id}/edit",
            '/admin/product-categories',
            '/admin/product-categories/create',
            "/admin/product-categories/{$productCategory->id}/edit",
            '/admin/products',
            '/admin/products/create',
            "/admin/products/{$product->id}",
            "/admin/products/{$product->id}/edit",
            '/admin/program-categories',
            '/admin/program-categories/create',
            "/admin/program-categories/{$programCategory->id}/edit",
            '/admin/programs',
            '/admin/programs/create',
            "/admin/programs/{$program->id}",
            "/admin/programs/{$program->id}/edit",
            '/admin/service-categories',
            '/admin/service-categories/create',
            "/admin/service-categories/{$serviceCategory->id}/edit",
            '/admin/service-offerings',
            '/admin/service-offerings/create',
            "/admin/service-offerings/{$serviceOffering->id}",
            "/admin/service-offerings/{$serviceOffering->id}/edit",
            '/admin/service-bookings',
            '/admin/service-bookings/create',
            "/admin/service-bookings/{$serviceBooking->id}",
            "/admin/service-bookings/{$serviceBooking->id}/edit",
            '/admin/message-departments',
            '/admin/message-departments/create',
            "/admin/message-departments/{$department->id}/edit",
            '/admin/customer-messages',
            '/admin/customer-messages/create',
            "/admin/customer-messages/{$message->id}",
            "/admin/customer-messages/{$message->id}/edit",
            '/admin/mobile-screens',
            '/admin/mobile-screens/create',
            "/admin/mobile-screens/{$screen->id}",
            "/admin/mobile-screens/{$screen->id}/edit",
        ];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk();
        }
    }
}
