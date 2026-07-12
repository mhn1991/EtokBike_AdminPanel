<?php

namespace Tests\Feature;

use App\Models\BikeProfile;
use App\Models\CustomerMessage;
use App\Models\CustomerProfile;
use App\Models\DeliveryZone;
use App\Models\DiscountCode;
use App\Models\MessageDepartment;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ServiceBooking;
use App\Models\ServiceCategory;
use App\Models\ServiceOffering;
use App\Models\ServiceTimeSlot;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontWebsiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_crawlable_and_lists_catalogue_products(): void
    {
        [$category, $product] = $this->createProduct();

        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('storefront.home').'">', false)
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('application/ld+json')
            ->assertSee($category->label)
            ->assertSee($product->title);
    }

    public function test_shop_filter_results_are_noindexed_to_protect_canonical_pages(): void
    {
        $this->createProduct();

        $this->get('/shop?q=ETX')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('storefront.shop').'">', false)
            ->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_parent_category_page_lists_products_from_nested_categories(): void
    {
        $parent = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        $child = ProductCategory::query()->create([
            'parent_id' => $parent->id,
            'slug' => 'mountain-bikes',
            'label' => 'دوچرخه کوهستان',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $child->id,
            'slug' => 'nested-mountain-bike',
            'title' => 'دوچرخه کوهستان nested',
            'subtitle' => 'محصول دسته فرزند',
            'availability' => 'in_stock',
            'price_value' => 18000000,
            'stock_quantity' => 3,
        ]);

        $this->get(route('storefront.categories.show', $parent))
            ->assertOk()
            ->assertSee($product->title)
            ->assertSee('دوچرخه کوهستان');

        $this->get(route('storefront.products.show', $product))
            ->assertOk()
            ->assertSee('دوچرخه')
            ->assertSee('دوچرخه کوهستان');
    }

    public function test_shop_filters_category_then_variant_features_and_price_range(): void
    {
        $accessories = ProductCategory::query()->create([
            'slug' => 'accessory',
            'label' => 'Accessory',
        ]);

        $tShirts = ProductCategory::query()->create([
            'parent_id' => $accessories->id,
            'slug' => 't-shirts',
            'label' => 'T-shirt',
        ]);

        $matching = Product::query()->create([
            'product_category_id' => $tShirts->id,
            'slug' => 'blue-xs-shirt',
            'title' => 'Blue XS shirt',
            'subtitle' => 'Matching accessory',
            'availability' => 'in_stock',
            'price_value' => 20000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $matching->id,
            'name' => 'Blue / XS',
            'options' => [
                'color' => 'blue',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $wrongSize = Product::query()->create([
            'product_category_id' => $tShirts->id,
            'slug' => 'blue-large-shirt',
            'title' => 'Blue large shirt',
            'subtitle' => 'Wrong size',
            'availability' => 'in_stock',
            'price_value' => 14000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $wrongSize->id,
            'name' => 'Blue / L',
            'options' => [
                'color' => 'blue',
                'size' => 'l',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $wrongBrand = Product::query()->create([
            'product_category_id' => $tShirts->id,
            'slug' => 'other-brand-blue-xs-shirt',
            'title' => 'Other brand blue XS shirt',
            'subtitle' => 'Wrong dynamic feature',
            'availability' => 'in_stock',
            'price_value' => 14000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $wrongBrand->id,
            'name' => 'Blue / XS',
            'options' => [
                'color' => 'blue',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Other',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $tooExpensive = Product::query()->create([
            'product_category_id' => $tShirts->id,
            'slug' => 'expensive-blue-xs-shirt',
            'title' => 'Expensive blue XS shirt',
            'subtitle' => 'Too expensive',
            'availability' => 'in_stock',
            'price_value' => 30000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $tooExpensive->id,
            'name' => 'Blue / XS',
            'options' => [
                'color' => 'blue',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 18000,
            'stock_quantity' => 3,
        ]);

        $otherCategory = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $bikeProduct = Product::query()->create([
            'product_category_id' => $otherCategory->id,
            'slug' => 'blue-xs-bike',
            'title' => 'Blue XS bike',
            'subtitle' => 'Wrong category',
            'availability' => 'in_stock',
            'price_value' => 14000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $bikeProduct->id,
            'name' => 'Blue / XS',
            'options' => [
                'color' => 'blue',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $this->get(route('storefront.categories.show', [
            'category' => $tShirts,
            'features' => [
                'brand' => 'Etok',
                'color' => 'blue',
                'size' => 'xs',
            ],
            'min_price' => 10000,
            'max_price' => 15000,
        ]))
            ->assertOk()
            ->assertSee('Blue XS shirt')
            ->assertSee('name="features[brand]"', false)
            ->assertSee('name="features[color]"', false)
            ->assertSee('name="features[size]"', false)
            ->assertSee('برند: Etok')
            ->assertSee('رنگ: blue')
            ->assertSee('سایز: xs')
            ->assertSee('قیمت از: 10,000 تومان')
            ->assertSee('قیمت تا: 15,000 تومان')
            ->assertDontSee('Blue large shirt')
            ->assertDontSee('Other brand blue XS shirt')
            ->assertDontSee('Expensive blue XS shirt')
            ->assertDontSee('Blue XS bike');
    }

    public function test_shop_feature_options_only_show_available_combinations(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'faceted-shirts',
            'label' => 'Faceted shirts',
        ]);

        $blueXs = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'facet-blue-xs',
            'title' => 'Facet Blue XS',
            'subtitle' => 'Available combination',
            'availability' => 'in_stock',
            'price_value' => 20000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $blueXs->id,
            'name' => 'Blue / XS / Etok',
            'options' => [
                'color' => 'blue',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $redLarge = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'facet-red-large',
            'title' => 'Facet Red Large',
            'subtitle' => 'Wrong size for selected filters',
            'availability' => 'in_stock',
            'price_value' => 14000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $redLarge->id,
            'name' => 'Red / L / Etok',
            'options' => [
                'color' => 'red',
                'size' => 'l',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $greenOther = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'facet-green-other',
            'title' => 'Facet Green Other',
            'subtitle' => 'Wrong brand for selected filters',
            'availability' => 'in_stock',
            'price_value' => 14000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $greenOther->id,
            'name' => 'Green / XS / Other',
            'options' => [
                'color' => 'green',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Other',
                ],
            ],
            'price_value' => 14000,
            'stock_quantity' => 3,
        ]);

        $blackExpensive = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'facet-black-expensive',
            'title' => 'Facet Black Expensive',
            'subtitle' => 'Too expensive for selected filters',
            'availability' => 'in_stock',
            'price_value' => 30000,
            'stock_quantity' => 5,
        ]);

        ProductVariant::query()->create([
            'product_id' => $blackExpensive->id,
            'name' => 'Black / XS / Etok',
            'options' => [
                'color' => 'black',
                'size' => 'xs',
                'attributes' => [
                    'brand' => 'Etok',
                ],
            ],
            'price_value' => 18000,
            'stock_quantity' => 3,
        ]);

        $this->get(route('storefront.categories.show', [
            'category' => $category,
            'features' => [
                'brand' => 'Etok',
                'size' => 'xs',
            ],
            'max_price' => 15000,
        ]))
            ->assertOk()
            ->assertSee('Facet Blue XS')
            ->assertSee('name="features[color]"', false)
            ->assertSee('<option value="blue"', false)
            ->assertDontSee('<option value="red"', false)
            ->assertDontSee('<option value="green"', false)
            ->assertDontSee('<option value="black"', false)
            ->assertDontSee('Facet Red Large')
            ->assertDontSee('Facet Green Other')
            ->assertDontSee('Facet Black Expensive');
    }

    public function test_product_page_exposes_canonical_url_and_product_json_ld(): void
    {
        [, $product] = $this->createProduct();

        $this->get(route('storefront.products.show', $product))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('storefront.products.show', $product).'">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"sku":"'.$product->slug.'"', false)
            ->assertSee($product->title);
    }

    public function test_sitemap_includes_shop_category_and_product_urls(): void
    {
        [$category, $product] = $this->createProduct();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('storefront.home'))
            ->assertSee(route('storefront.shop'))
            ->assertSee(route('storefront.categories.show', $category))
            ->assertSee(route('storefront.products.show', $product));
    }

    public function test_checkout_creates_admin_order_from_website_cart(): void
    {
        [, $product] = $this->createProduct();

        $this->post(route('storefront.cart.items.store', $product), [
            'quantity' => 2,
        ])->assertRedirect(route('storefront.cart.show'));

        $response = $this->post(route('storefront.checkout.store'), [
            'customer_name' => 'Website Customer',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '+989120000000',
            'fulfillment_method' => 'pickup',
            'customer_notes' => 'Website test order.',
        ]);

        $order = Order::query()->first();

        $response->assertRedirect(route('storefront.checkout.success', $order));

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Website Customer',
            'customer_email' => 'customer@example.com',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 57000000,
            'total' => 57000000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->slug,
            'title' => $product->title,
            'quantity' => 2,
            'line_total' => 57000000,
        ]);
    }

    public function test_services_page_creates_admin_service_booking(): void
    {
        $category = ServiceCategory::query()->create([
            'slug' => 'maintenance',
            'label' => 'Maintenance',
            'title' => 'Maintenance services',
        ]);

        ServiceOffering::query()->create([
            'service_category_id' => $category->id,
            'slug' => 'full-service',
            'title' => 'سرویس کامل',
            'subtitle' => 'تنظیم کامل دوچرخه',
        ]);

        ServiceTimeSlot::query()->create(['label' => 'فردا ۱۰:۳۰']);

        $this->get(route('storefront.services'))
            ->assertOk()
            ->assertSee('سرویس کامل');

        $this->post(route('storefront.services.bookings.store'), [
            'customer_name' => 'Service Customer',
            'customer_phone' => '+989121111111',
            'customer_email' => 'service@example.com',
            'service_type' => 'سرویس کامل',
            'bike_label' => 'ETX 200',
            'preferred_time' => 'فردا ۱۰:۳۰',
        ])->assertRedirect(route('storefront.services'));

        $this->assertDatabaseHas('service_bookings', [
            'customer_name' => 'Service Customer',
            'service_type' => 'سرویس کامل',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('customer_profiles', [
            'email' => 'service@example.com',
            'phone' => '+989121111111',
        ]);
    }

    public function test_events_page_creates_program_booking(): void
    {
        $category = ProgramCategory::query()->create([
            'slug' => 'rides',
            'label' => 'Rides',
            'title' => 'Ride programs',
        ]);

        $program = Program::query()->create([
            'program_category_id' => $category->id,
            'slug' => 'friday-ride',
            'title' => 'Friday Ride',
            'subtitle' => 'Morning route',
            'date_value' => '2026-07-01',
            'date_label' => 'Friday morning',
            'program_state' => 'future',
            'capacity' => 4,
        ]);

        $this->get(route('storefront.events'))
            ->assertOk()
            ->assertSee('Friday Ride');

        $this->post(route('storefront.events.bookings.store', $program), [
            'customer_name' => 'Program Customer',
            'customer_phone' => '+989122222222',
            'customer_email' => 'program@example.com',
            'attendees' => 2,
        ])->assertRedirect(route('storefront.events.show', $program));

        $this->assertDatabaseHas('program_bookings', [
            'program_id' => $program->id,
            'customer_name' => 'Program Customer',
            'attendees' => 2,
            'status' => 'pending',
        ]);

        $this->assertSame(2, $program->fresh()->reserved_count);
    }

    public function test_messages_page_creates_customer_message(): void
    {
        $department = MessageDepartment::query()->create([
            'slug' => 'support',
            'title' => 'Support',
            'thread_title' => 'Support thread',
            'composer_title' => 'Message support',
            'is_active' => true,
        ]);

        $this->post(route('storefront.messages.store'), [
            'message_department_id' => $department->id,
            'customer_name' => 'Message Customer',
            'customer_phone' => '+989123333333',
            'customer_email' => 'message@example.com',
            'text' => 'Need help with an order.',
        ])->assertRedirect(route('storefront.messages'));

        $this->assertDatabaseHas('customer_messages', [
            'message_department_id' => $department->id,
            'sender' => 'client',
            'is_unread' => true,
        ]);

        $this->assertTrue(CustomerMessage::query()->where('text', 'like', '%Need help%')->exists());
    }

    public function test_account_lookup_shows_customer_orders_services_and_bikes(): void
    {
        [$category, $product] = $this->createProduct();

        $profile = CustomerProfile::query()->create([
            'name' => 'Lookup Customer',
            'phone' => '+989124444444',
            'email' => 'lookup@example.com',
        ]);

        BikeProfile::query()->create([
            'customer_profile_id' => $profile->id,
            'title' => 'Lookup Bike',
            'subtitle' => 'Road bike',
        ]);

        $order = Order::query()->create([
            'customer_name' => 'Lookup Customer',
            'customer_phone' => '+989124444444',
            'customer_email' => 'lookup@example.com',
            'status' => 'processing',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $order->items()->create([
            'product_id' => $product->slug,
            'title' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price_value,
        ]);

        ServiceBooking::query()->create([
            'customer_name' => 'Lookup Customer',
            'customer_phone' => '+989124444444',
            'customer_email' => 'lookup@example.com',
            'service_type' => 'Tune up',
            'status' => 'confirmed',
        ]);

        $this->get(route('storefront.account', ['phone' => '+989124444444']))
            ->assertOk()
            ->assertSee('Lookup Customer')
            ->assertSee($order->order_number)
            ->assertSee('Tune up')
            ->assertSee('Lookup Bike');
    }

    public function test_checkout_applies_delivery_discount_payment_and_shipment_records(): void
    {
        [, $product] = $this->createProduct();

        $zone = DeliveryZone::query()->create([
            'name' => 'Central',
            'code' => 'central',
            'fee' => 500000,
        ]);

        DiscountCode::query()->create([
            'code' => 'ETOK10',
            'name' => 'Ten percent',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->post(route('storefront.cart.items.store', $product), [
            'quantity' => 1,
        ])->assertRedirect(route('storefront.cart.show'));

        $this->post(route('storefront.checkout.store'), [
            'customer_name' => 'Delivery Customer',
            'customer_email' => 'delivery@example.com',
            'customer_phone' => '+989125555555',
            'fulfillment_method' => 'delivery',
            'delivery_zone_id' => $zone->id,
            'delivery_address' => 'Tehran address',
            'discount_code' => 'ETOK10',
            'payment_method' => 'bank_transfer',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Delivery Customer',
            'discount_total' => 2850000,
            'delivery_total' => 500000,
            'total' => 26150000,
        ]);

        $order = Order::query()->where('customer_email', 'delivery@example.com')->firstOrFail();

        $this->assertTrue(Shipment::query()->where('order_id', $order->id)->exists());
        $this->assertTrue(PaymentTransaction::query()->where('order_id', $order->id)->where('provider', 'bank_transfer')->exists());
    }

    /**
     * @return array{ProductCategory, Product}
     */
    private function createProduct(): array
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-etx-200',
            'title' => 'دوچرخه کوهستان ETX 200',
            'subtitle' => 'فریم مقاوم و ترمز دیسکی',
            'description' => 'گزینه مطمئن برای مسیرهای ترکیبی، تمرین آخر هفته و شروع کوهستان.',
            'availability' => 'low_stock',
            'price_value' => 28500000,
            'price_label' => '۲۸,۵۰۰,۰۰۰ تومان',
            'stock_label' => 'موجودی محدود',
            'stock_quantity' => 4,
            'minimum_stock' => 2,
            'thumbnail_text' => 'MTB',
            'thumbnail_color' => '#101114',
            'is_featured' => true,
            'is_active' => true,
        ]);

        return [$category, $product];
    }
}
