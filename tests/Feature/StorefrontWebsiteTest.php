<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
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
