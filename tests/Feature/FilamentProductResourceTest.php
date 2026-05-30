<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_products_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-test',
            'title' => 'دوچرخه تست',
            'subtitle' => 'محصول تست',
            'availability' => 'in_stock',
            'price_value' => 1000000,
        ]);

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertOk()
            ->assertSee('دوچرخه تست');
    }

    public function test_the_product_categories_resource_renders_in_the_admin_panel(): void
    {
        $user = User::factory()->create();

        ProductCategory::query()->create([
            'slug' => 'parts',
            'label' => 'قطعات',
        ]);

        $this->actingAs($user)
            ->get('/admin/product-categories')
            ->assertOk()
            ->assertSee('قطعات');
    }
}
