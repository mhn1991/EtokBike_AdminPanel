<?php

namespace Tests\Feature;

use App\Models\BikeProfile;
use App\Models\CustomerMessage;
use App\Models\CustomerProfile;
use App\Models\DeliveryMethod;
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
use App\Models\ServiceTimeSlot;
use App\Models\StoreProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MobileConfigApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_mobile_manifest_with_remote_screen_urls(): void
    {
        config(['mobile.base_url' => 'http://10.0.2.2:8001']);

        $response = $this->getJson('/api/mobile/manifest');

        $response
            ->assertOk()
            ->assertJsonPath('schemaVersion', 1)
            ->assertJsonPath('appId', 'etokbike')
            ->assertJsonPath('remoteConfig.manifestUrl', 'http://10.0.2.2:8001/api/mobile/manifest')
            ->assertJsonPath('remoteConfig.telemetryUrl', 'http://10.0.2.2:8001/api/mobile/telemetry')
            ->assertJsonPath('screens.home.url', 'http://10.0.2.2:8001/api/mobile/screens/home');
    }

    public function test_it_returns_every_configured_mobile_screen_payload(): void
    {
        $manifest = json_decode(File::get(resource_path('mobile/manifest.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach (array_keys($manifest['screens']) as $screenId) {
            $this->getJson("/api/mobile/screens/{$screenId}")
                ->assertOk()
                ->assertJsonPath('schemaVersion', 1)
                ->assertJsonPath('screenId', $screenId)
                ->assertJsonStructure([
                    'schemaVersion',
                    'screenId',
                    'version',
                    'title',
                    'sections',
                ]);
        }
    }

    public function test_it_returns_not_found_for_unknown_mobile_screens(): void
    {
        $this->getJson('/api/mobile/screens/unknown')
            ->assertNotFound();
    }

    public function test_it_returns_programs_from_the_database_for_the_events_screen(): void
    {
        $category = ProgramCategory::query()->create([
            'slug' => 'training',
            'label' => 'آموزش',
            'title' => 'کلاس‌های آموزشی',
        ]);

        Program::query()->create([
            'program_category_id' => $category->id,
            'slug' => 'training-test',
            'title' => 'کلاس تست',
            'subtitle' => 'تست اتصال پنل به اپ',
            'date_value' => '2026-06-02',
            'date_label' => 'سه‌شنبه ۱۲ خرداد ۱۴۰۵، ساعت ۱۸',
            'program_state' => 'future',
            'thumbnail_text' => 'TEST',
        ]);

        $this->getJson('/api/mobile/screens/events')
            ->assertOk()
            ->assertJsonPath('screenId', 'events')
            ->assertJsonPath('sections.0.type', 'hero')
            ->assertJsonPath('sections.2.data.defaultSubsection', 'training')
            ->assertJsonPath('sections.2.data.subsections.0.items.0.id', 'training-test')
            ->assertJsonPath('sections.2.data.subsections.0.items.0.title', 'کلاس تست');
    }

    public function test_it_returns_products_from_the_database_for_the_shop_screen(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-panel-test',
            'title' => 'دوچرخه پنل',
            'subtitle' => 'نمایش از دیتابیس',
            'description' => 'محصول تستی برای اتصال پنل به اپ',
            'availability' => 'in_stock',
            'price_value' => 1230000,
            'price_label' => '۱,۲۳۰,۰۰۰ تومان',
            'thumbnail_text' => 'TEST',
            'image_url' => 'mobile/products/bike-panel-test.jpg',
        ]);

        $this->getJson('/api/mobile/screens/shop')
            ->assertOk()
            ->assertJsonPath('screenId', 'shop')
            ->assertJsonPath('sections.0.type', 'hero')
            ->assertJsonPath('sections.2.data.defaultCategory', 'bikes')
            ->assertJsonPath('sections.2.data.categories.0.id', 'bikes')
            ->assertJsonPath('sections.2.data.items.0.id', 'bike-panel-test')
            ->assertJsonPath('sections.2.data.items.0.title', 'دوچرخه پنل')
            ->assertJsonPath('sections.2.data.items.0.imageUrl', 'http://127.0.0.1:8001/storage/mobile/products/bike-panel-test.jpg');
    }

    public function test_it_returns_services_from_the_database_for_the_services_screen(): void
    {
        $category = ServiceCategory::query()->create([
            'slug' => 'maintenance',
            'label' => 'سرویس',
            'title' => 'سرویس و تنظیمات',
        ]);

        ServiceOffering::query()->create([
            'service_category_id' => $category->id,
            'slug' => 'full-service',
            'title' => 'سرویس کامل تست',
            'subtitle' => 'نمایش از دیتابیس',
            'price_label' => 'از ۹۵۰,۰۰۰ تومان',
        ]);

        ServiceBooking::query()->create([
            'customer_name' => 'Mobile Customer',
            'service_type' => 'سرویس کامل تست',
            'bike_label' => 'دوچرخه مشتری',
            'status' => 'pending',
        ]);

        ServiceTimeSlot::query()->create([
            'label' => 'شنبه ۱۰:۰۰',
            'sort_order' => 1,
        ]);

        $this->getJson('/api/mobile/screens/services')
            ->assertOk()
            ->assertJsonPath('screenId', 'services')
            ->assertJsonPath('sections.1.data.defaultSubsection', 'maintenance')
            ->assertJsonPath('sections.1.data.subsections.0.items.0.title', 'سرویس کامل تست')
            ->assertJsonPath('sections.2.data.services.0', 'سرویس کامل تست')
            ->assertJsonPath('sections.2.data.timeSlots.0', 'شنبه ۱۰:۰۰')
            ->assertJsonPath('sections.3.data.items.0.title', 'سرویس کامل تست');
    }

    public function test_it_returns_messages_from_the_database_for_the_messages_screen(): void
    {
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
            'text' => 'پیام تست برای اپ',
            'is_unread' => true,
        ]);

        $this->getJson('/api/mobile/screens/messages')
            ->assertOk()
            ->assertJsonPath('screenId', 'messages')
            ->assertJsonPath('sections.0.type', 'hero')
            ->assertJsonPath('sections.1.data.defaultDepartment', 'support')
            ->assertJsonPath('sections.1.data.departments.0.messages.0.text', 'پیام تست برای اپ');
    }

    public function test_it_returns_home_content_from_database(): void
    {
        StoreProfile::query()->create([
            'status_title' => 'Open for test',
            'status_subtitle' => 'Testing store status',
            'status_description' => 'Status from admin.',
            'status_label' => 'Open',
            'branch_title' => 'Test branch',
            'address' => 'Test address',
            'hours' => '10-20',
            'action_label' => 'Call',
        ]);

        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'featured-home-bike',
            'title' => 'دوچرخه ویژه خانه',
            'subtitle' => 'نمایش در صفحه اصلی',
            'availability' => 'in_stock',
            'price_value' => 25000000,
            'thumbnail_text' => 'HOME',
            'is_featured' => true,
        ]);

        $this->getJson('/api/mobile/screens/home')
            ->assertOk()
            ->assertJsonPath('screenId', 'home')
            ->assertJsonPath('sections.2.data.items.0.id', 'featured-home-bike')
            ->assertJsonPath('sections.2.data.items.0.title', 'دوچرخه ویژه خانه')
            ->assertJsonPath('sections.4.data.items.0.title', 'Open for test')
            ->assertJsonPath('sections.7.data.items.0.title', 'Test branch');
    }

    public function test_it_returns_account_content_from_database(): void
    {
        $profile = CustomerProfile::query()->create([
            'name' => 'Panel Customer',
            'phone' => '+989121111111',
            'email' => 'panel@example.com',
            'delivery_address' => 'Tehran test address',
        ]);

        BikeProfile::query()->create([
            'customer_profile_id' => $profile->id,
            'title' => 'Panel Bike',
            'subtitle' => 'Admin maintained bike',
            'frame_size' => 'M',
            'tire_size' => '29',
            'brake_type' => 'Disc',
            'next_recommendation' => 'Brake check',
        ]);

        $order = Order::query()->create([
            'customer_name' => 'Mobile Customer',
            'customer_phone' => '+989120000000',
            'status' => 'processing',
            'payment_status' => 'unpaid',
        ]);

        $order->items()->create([
            'title' => 'دوچرخه تست حساب',
            'quantity' => 1,
            'unit_price' => 1200000,
        ]);

        $this->getJson('/api/mobile/screens/account')
            ->assertOk()
            ->assertJsonPath('screenId', 'account')
            ->assertJsonPath('sections.1.data.title', 'سلام، Panel Customer')
            ->assertJsonPath('sections.2.data.items.0.title', 'سفارش '.$order->order_number)
            ->assertJsonPath('sections.3.data.items.0.title', 'Panel Bike')
            ->assertJsonPath('sections.4.data.fields.3.value', 'Tehran test address')
            ->assertJsonPath('sections.5.data.items', []);
    }

    public function test_it_returns_cart_content_from_database_products(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'parts',
            'label' => 'قطعات',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'cart-product-test',
            'title' => 'محصول سبد تست',
            'subtitle' => 'نمایش در سبد',
            'availability' => 'in_stock',
            'price_value' => 1200000,
            'thumbnail_text' => 'CART',
            'is_featured' => true,
        ]);

        DeliveryMethod::query()->create([
            'title' => 'Pickup test',
            'subtitle' => 'Store pickup',
            'description' => 'Test delivery method',
            'price_label' => 'Free',
        ]);

        $this->getJson('/api/mobile/screens/cart')
            ->assertOk()
            ->assertJsonPath('screenId', 'cart')
            ->assertJsonPath('sections.1.data.items.0.title', 'محصول سبد تست')
            ->assertJsonPath('sections.1.data.total', '1,200,000 تومان')
            ->assertJsonPath('sections.2.data.items.0.title', 'Pickup test');
    }

    public function test_it_returns_mobile_page_sections_from_the_database(): void
    {
        $screen = MobileScreen::query()->create([
            'screen_id' => 'home',
            'title' => 'صفحه خانه پنل',
            'version' => 42,
            'hide_title' => true,
            'is_active' => true,
        ]);

        $screen->sections()->createMany([
            [
                'section_id' => 'panel-hero',
                'type' => 'hero',
                'data' => [
                    'title' => 'عنوان قابل ویرایش از پنل',
                    'subtitle' => 'زیرعنوان قابل ویرایش',
                    'actionLabel' => 'رفتن به فروشگاه',
                    'target' => 'shop',
                ],
                'layout' => ['variant' => 'standard'],
                'style' => ['emphasis' => 'high'],
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'section_id' => 'hidden-section',
                'type' => 'business_info',
                'data' => ['title' => 'پنهان', 'items' => []],
                'sort_order' => 1,
                'is_active' => false,
            ],
        ]);

        $this->getJson('/api/mobile/screens/home')
            ->assertOk()
            ->assertJsonPath('screenId', 'home')
            ->assertJsonPath('title', 'صفحه خانه پنل')
            ->assertJsonPath('hideTitle', true)
            ->assertJsonPath('sections.0.id', 'panel-hero')
            ->assertJsonPath('sections.0.data.title', 'عنوان قابل ویرایش از پنل')
            ->assertJsonMissingPath('sections.1');
    }
}
