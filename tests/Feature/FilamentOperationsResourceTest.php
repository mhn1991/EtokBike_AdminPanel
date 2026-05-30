<?php

namespace Tests\Feature;

use App\Models\CustomerMessage;
use App\Models\MessageDepartment;
use App\Models\ServiceBooking;
use App\Models\ServiceCategory;
use App\Models\ServiceOffering;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentOperationsResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_resources_render_in_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $category = ServiceCategory::query()->create([
            'slug' => 'maintenance',
            'label' => 'سرویس',
            'title' => 'سرویس و تنظیمات',
        ]);

        ServiceOffering::query()->create([
            'service_category_id' => $category->id,
            'slug' => 'full-service',
            'title' => 'سرویس کامل دوچرخه',
            'subtitle' => 'تنظیم دنده و ترمز',
        ]);

        ServiceBooking::query()->create([
            'customer_name' => 'Service Customer',
            'service_type' => 'سرویس کامل',
            'status' => 'pending',
        ]);

        $this->actingAs($user)->get('/admin/service-categories')->assertOk()->assertSee('سرویس');
        $this->actingAs($user)->get('/admin/service-offerings')->assertOk()->assertSee('سرویس کامل دوچرخه');
        $this->actingAs($user)->get('/admin/service-bookings')->assertOk()->assertSee('Service Customer');
    }

    public function test_message_resources_render_in_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $department = MessageDepartment::query()->create([
            'slug' => 'support',
            'title' => 'پشتیبانی سفارش',
            'thread_title' => 'گفتگو با پشتیبانی سفارش',
            'composer_title' => 'ارسال پیام به پشتیبانی سفارش',
        ]);

        CustomerMessage::query()->create([
            'message_department_id' => $department->id,
            'sender' => 'department',
            'label' => 'پشتیبانی سفارش',
            'text' => 'پیام تست',
        ]);

        $this->actingAs($user)->get('/admin/message-departments')->assertOk()->assertSee('پشتیبانی سفارش');
        $this->actingAs($user)->get('/admin/customer-messages')->assertOk()->assertSee('پشتیبانی سفارش');
    }
}
