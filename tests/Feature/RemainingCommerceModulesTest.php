<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\ContentPage;
use App\Models\DiscountCode;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\SeoRedirect;
use App\Models\SeoSetting;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemainingCommerceModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_remaining_module_admin_screens_render(): void
    {
        $user = User::factory()->create();

        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'variant-bike',
            'title' => 'Variant Bike',
            'subtitle' => 'Variant product',
            'availability' => 'in_stock',
            'price_value' => 1000000,
            'stock_quantity' => 10,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => 'Red / Large',
            'sku' => 'VARIANT-RED-L',
            'stock_quantity' => 3,
        ]);

        $seoSetting = SeoSetting::query()->create([
            'site_name' => 'EtokBike',
            'is_active' => true,
        ]);

        $redirect = SeoRedirect::query()->create([
            'source_path' => '/old-shop',
            'target_url' => '/shop',
            'status_code' => 301,
        ]);

        $page = ContentPage::query()->create([
            'slug' => 'faq',
            'title' => 'FAQ',
            'body' => 'FAQ body',
        ]);

        $order = Order::query()->create([
            'customer_name' => 'Payment Customer',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_method' => 'pickup',
        ]);

        $payment = PaymentTransaction::query()->create([
            'order_id' => $order->id,
            'provider' => 'manual',
            'status' => 'pending',
            'amount' => 1000000,
        ]);

        $taxRate = TaxRate::query()->create([
            'name' => 'VAT',
            'code' => 'VAT',
            'rate' => 9,
        ]);

        $discount = DiscountCode::query()->create([
            'code' => 'SAVE10',
            'name' => 'Save 10',
            'type' => 'fixed',
            'value' => 100000,
        ]);

        $template = NotificationTemplate::query()->create([
            'key' => 'order-confirmation',
            'channel' => 'email',
            'subject' => 'Order confirmed',
            'body' => 'Your order is confirmed.',
        ]);

        $notification = NotificationLog::query()->create([
            'notification_template_id' => $template->id,
            'order_id' => $order->id,
            'channel' => 'email',
            'recipient' => 'customer@example.com',
            'status' => 'pending',
        ]);

        $activity = AdminActivityLog::query()->create([
            'user_id' => $user->id,
            'event' => 'created',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);

        $paths = [
            '/admin/seo-settings',
            '/admin/seo-settings/create',
            "/admin/seo-settings/{$seoSetting->id}/edit",
            '/admin/seo-redirects',
            '/admin/seo-redirects/create',
            "/admin/seo-redirects/{$redirect->id}/edit",
            '/admin/content-pages',
            '/admin/content-pages/create',
            "/admin/content-pages/{$page->id}/edit",
            '/admin/seo-audit',
            '/admin/product-variants',
            '/admin/product-variants/create',
            "/admin/product-variants/{$variant->id}/edit",
            '/admin/payment-transactions',
            '/admin/payment-transactions/create',
            "/admin/payment-transactions/{$payment->id}/edit",
            '/admin/tax-rates',
            '/admin/tax-rates/create',
            "/admin/tax-rates/{$taxRate->id}/edit",
            '/admin/discount-codes',
            '/admin/discount-codes/create',
            "/admin/discount-codes/{$discount->id}/edit",
            '/admin/notification-templates',
            '/admin/notification-templates/create',
            "/admin/notification-templates/{$template->id}/edit",
            '/admin/notification-logs',
            '/admin/notification-logs/create',
            "/admin/notification-logs/{$notification->id}/edit",
            '/admin/admin-activity-logs',
        ];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk();
        }

        $this->assertTrue(AdminActivityLog::query()->whereKey($activity->id)->exists());
    }
}
