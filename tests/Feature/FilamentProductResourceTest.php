<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductCategories\Pages\CreateProductCategory;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

    public function test_product_can_be_created_with_variants_from_the_product_form(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        $this->actingAs($user);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'product_category_id' => $category->id,
                'title' => 'دوچرخه شهری',
                'subtitle' => 'قابل سفارش در چند رنگ',
                'availability' => 'in_stock',
                'slug' => 'city-bike',
                'sku' => 'CITY-BASE',
                'sort_order' => 0,
                'is_featured' => false,
                'is_active' => true,
                'price_value' => 12000000,
                'reserved_quantity' => 0,
                'minimum_stock' => 1,
                'warehouse_location' => 'A1',
                'thumbnail_text' => 'CITY',
                'thumbnail_color' => '#101114',
                'robots' => 'index,follow',
                'include_in_sitemap' => true,
                'sitemap_priority' => 0.7,
                'sitemap_change_frequency' => 'weekly',
                'variants' => [
                    [
                        'name' => 'قرمز / بزرگ',
                        'sku' => 'CITY-RED-L',
                        'options' => [
                            'color' => 'قرمز',
                            'size' => 'L',
                        ],
                        'price_value' => 12500000,
                        'stock_quantity' => 4,
                        'minimum_stock' => 1,
                        'is_active' => true,
                    ],
                    [
                        'name' => 'مشکی / متوسط',
                        'sku' => 'CITY-BLK-M',
                        'options' => [
                            'color' => 'مشکی',
                            'size' => 'M',
                        ],
                        'price_value' => null,
                        'stock_quantity' => 2,
                        'minimum_stock' => 1,
                        'is_active' => true,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('slug', 'city-bike')->firstOrFail();

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => 'قرمز / بزرگ',
            'sku' => 'CITY-RED-L',
            'price_value' => 12500000,
            'stock_quantity' => 4,
            'minimum_stock' => 1,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'name' => 'مشکی / متوسط',
            'sku' => 'CITY-BLK-M',
            'price_value' => null,
            'stock_quantity' => 2,
            'minimum_stock' => 1,
            'sort_order' => 2,
        ]);

        $this->assertSame(
            ['color' => 'قرمز', 'size' => 'L'],
            $product->variants()->where('sku', 'CITY-RED-L')->firstOrFail()->options,
        );
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

    public function test_product_category_can_be_created_under_a_parent_category(): void
    {
        $user = User::factory()->create();
        $parent = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'دوچرخه',
        ]);

        $this->actingAs($user);

        Livewire::test(CreateProductCategory::class)
            ->fillForm([
                'parent_id' => $parent->id,
                'label' => 'دوچرخه کوهستان',
                'slug' => 'mountain-bikes',
                'sort_order' => 1,
                'is_active' => true,
                'robots' => 'index,follow',
                'include_in_sitemap' => true,
                'sitemap_priority' => 0.8,
                'sitemap_change_frequency' => 'weekly',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('product_categories', [
            'parent_id' => $parent->id,
            'slug' => 'mountain-bikes',
            'label' => 'دوچرخه کوهستان',
        ]);

        $this->actingAs($user)
            ->get('/admin/product-categories')
            ->assertOk()
            ->assertSee('دوچرخه کوهستان')
            ->assertSee('دوچرخه');
    }
}
