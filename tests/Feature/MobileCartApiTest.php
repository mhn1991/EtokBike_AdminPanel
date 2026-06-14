<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_updates_and_removes_cart_items(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-cart-test',
            'title' => 'Bike Cart Test',
            'subtitle' => 'Cart product',
            'availability' => 'in_stock',
            'price_value' => 1000,
            'stock_quantity' => 10,
        ]);

        $addResponse = $this->postJson('/api/cart/items', [
            'device_id' => 'device-1',
            'product' => 'bike-cart-test',
            'quantity' => 2,
        ]);

        $addResponse
            ->assertCreated()
            ->assertJsonPath('data.count', 2)
            ->assertJsonPath('data.total', 2000)
            ->assertJsonPath('data.items.0.product.id', 'bike-cart-test');

        $itemId = $addResponse->json('data.items.0.id');

        $this->patchJson("/api/cart/items/{$itemId}", [
            'device_id' => 'device-1',
            'quantity' => 3,
        ])
            ->assertOk()
            ->assertJsonPath('data.count', 3)
            ->assertJsonPath('data.total', 3000);

        $this->deleteJson("/api/cart/items/{$itemId}", [
            'device_id' => 'device-1',
        ])
            ->assertOk()
            ->assertJsonPath('data.count', 0)
            ->assertJsonPath('data.items', []);
    }

    public function test_mobile_state_returns_cart_and_unread_counts(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-state-test',
            'title' => 'Bike State Test',
            'subtitle' => 'State product',
            'availability' => 'in_stock',
            'price_value' => 1000,
            'stock_quantity' => 10,
        ]);

        $this->postJson('/api/cart/items', [
            'device_id' => 'device-1',
            'product' => 'bike-state-test',
            'quantity' => 2,
        ])->assertCreated();

        $this->getJson('/api/mobile/state?device_id=device-1')
            ->assertOk()
            ->assertJsonPath('data.cart_count', 2)
            ->assertJsonPath('data.unread_message_count', 0);
    }

    public function test_authenticated_cart_is_scoped_to_user(): void
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'bike-user-cart-test',
            'title' => 'Bike User Cart Test',
            'subtitle' => 'User cart product',
            'availability' => 'in_stock',
            'price_value' => 1000,
            'stock_quantity' => 10,
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('android')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/cart/items', [
                'device_id' => 'device-a',
                'product' => 'bike-user-cart-test',
                'quantity' => 2,
            ])
            ->assertCreated()
            ->assertJsonPath('data.count', 2);

        $this->withToken($token)
            ->getJson('/api/cart?device_id=device-b')
            ->assertOk()
            ->assertJsonPath('data.count', 2);

        $otherUser = User::factory()->create();
        $otherToken = $otherUser->createToken('android')->plainTextToken;

        $this->flushHeaders()
            ->withHeaders(['Authorization' => 'Bearer '.$otherToken])
            ->getJson('/api/cart?device_id=device-b')
            ->assertOk()
            ->assertJsonPath('data.count', 0);
    }
}
