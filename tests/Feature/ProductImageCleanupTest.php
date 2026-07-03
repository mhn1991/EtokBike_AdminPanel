<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_the_previous_product_image_when_replaced(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('mobile/products/old.jpg', 'old');
        Storage::disk('public')->put('mobile/products/new.jpg', 'new');

        $product = $this->createProduct([
            'slug' => 'image-replace-test',
            'image_url' => 'mobile/products/old.jpg',
        ]);

        $product->update(['image_url' => 'mobile/products/new.jpg']);

        Storage::disk('public')->assertMissing('mobile/products/old.jpg');
        Storage::disk('public')->assertExists('mobile/products/new.jpg');
    }

    public function test_it_deletes_the_previous_product_image_when_removed(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('mobile/products/remove.jpg', 'remove');

        $product = $this->createProduct([
            'slug' => 'image-remove-test',
            'image_url' => 'mobile/products/remove.jpg',
        ]);

        $product->update(['image_url' => null]);

        Storage::disk('public')->assertMissing('mobile/products/remove.jpg');
    }

    public function test_it_keeps_a_previous_image_when_another_product_still_uses_it(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('mobile/products/shared.jpg', 'shared');

        $firstProduct = $this->createProduct([
            'slug' => 'shared-image-first',
            'image_url' => 'mobile/products/shared.jpg',
        ]);

        $this->createProduct([
            'slug' => 'shared-image-second',
            'image_url' => 'mobile/products/shared.jpg',
        ]);

        $firstProduct->update(['image_url' => null]);

        Storage::disk('public')->assertExists('mobile/products/shared.jpg');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createProduct(array $attributes): Product
    {
        $category = ProductCategory::query()->firstOrCreate(
            ['slug' => 'bikes'],
            ['label' => 'دوچرخه'],
        );

        return Product::query()->create(array_merge([
            'product_category_id' => $category->id,
            'slug' => 'cleanup-test',
            'title' => 'Cleanup test product',
            'subtitle' => 'Image cleanup',
            'availability' => 'in_stock',
            'price_value' => 1000,
            'thumbnail_text' => 'TEST',
        ], $attributes));
    }
}
