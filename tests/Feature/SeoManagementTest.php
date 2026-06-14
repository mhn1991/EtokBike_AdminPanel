<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoRedirect;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_pages_use_admin_controlled_seo_fields(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'seo-bike',
            'title' => 'SEO Bike',
            'subtitle' => 'Fallback subtitle',
            'description' => 'Fallback description',
            'availability' => 'in_stock',
            'price_value' => 1000000,
            'seo_title' => 'Custom SEO Bike Title',
            'seo_description' => 'Custom SEO Bike Description',
            'robots' => 'noindex,follow',
            'og_title' => 'Custom OG Bike Title',
            'og_description' => 'Custom OG Bike Description',
            'include_in_sitemap' => false,
        ]);

        $this->get(route('storefront.products.show', $product))
            ->assertOk()
            ->assertSee('<title>Custom SEO Bike Title</title>', false)
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<meta name="description" content="Custom SEO Bike Description">', false)
            ->assertSee('<meta property="og:title" content="Custom OG Bike Title">', false)
            ->assertSee('<meta property="og:description" content="Custom OG Bike Description">', false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('storefront.products.show', $product));
    }

    public function test_content_pages_and_redirects_are_seo_managed(): void
    {
        SeoSetting::query()->create([
            'site_name' => 'EtokBike SEO',
            'default_title' => 'Default SEO Title',
            'default_description' => 'Default SEO Description',
            'is_active' => true,
        ]);

        $page = ContentPage::query()->create([
            'slug' => 'about-etokbike',
            'title' => 'About EtokBike',
            'excerpt' => 'About excerpt',
            'body' => 'About body',
            'seo_title' => 'About SEO Title',
            'seo_description' => 'About SEO Description',
            'robots' => 'index,follow',
            'include_in_sitemap' => true,
            'sitemap_priority' => 0.6,
            'sitemap_change_frequency' => 'monthly',
            'is_active' => true,
        ]);

        SeoRedirect::query()->create([
            'source_path' => 'old-about',
            'target_url' => route('storefront.pages.show', $page),
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get(route('storefront.pages.show', $page))
            ->assertOk()
            ->assertSee('<title>About SEO Title</title>', false)
            ->assertSee('<meta name="description" content="About SEO Description">', false)
            ->assertSee('About body');

        $this->get('/old-about')
            ->assertRedirect(route('storefront.pages.show', $page));

        $this->assertSame(1, SeoRedirect::query()->firstOrFail()->hit_count);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('storefront.pages.show', $page));
    }
}
