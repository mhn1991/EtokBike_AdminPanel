<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Receipt;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Support\Receipts\ReceiptGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceiptGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_an_order_receipt_with_snapshot_items(): void
    {
        [$order] = $this->createOrderWithItem();

        $receipt = app(ReceiptGenerator::class)->forOrder($order);
        $secondReceipt = app(ReceiptGenerator::class)->forOrder($order);

        $this->assertTrue($receipt->is($secondReceipt));
        $this->assertStringStartsWith('RCP-', $receipt->receipt_number);
        $this->assertSame('receipt', $receipt->type);
        $this->assertSame('issued', $receipt->status);
        $this->assertSame(2000000, $receipt->subtotal);
        $this->assertSame(2000000, $receipt->total);

        $this->assertDatabaseHas('receipt_items', [
            'receipt_id' => $receipt->id,
            'title' => 'Receipt Bike',
            'sku' => 'RECEIPT-BIKE',
            'quantity' => 2,
            'unit_price' => 1000000,
            'line_total' => 2000000,
        ]);
    }

    public function test_it_generates_a_credit_note_from_a_return_request(): void
    {
        [$order, $product] = $this->createOrderWithItem();
        $return = ReturnRequest::query()->create([
            'order_id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'status' => 'received',
            'refund_status' => 'pending',
        ]);

        $return->items()->create([
            'product_id' => $product->id,
            'title' => $product->title,
            'quantity' => 1,
            'unit_price' => 1000000,
            'condition' => 'sellable',
            'should_restock' => true,
        ]);

        $creditNote = app(ReceiptGenerator::class)->creditNoteForReturn($return);

        $this->assertStringStartsWith('CRN-', $creditNote->receipt_number);
        $this->assertSame('credit_note', $creditNote->type);
        $this->assertSame(1000000, $creditNote->total);
        $this->assertDatabaseHas('receipt_items', [
            'receipt_id' => $creditNote->id,
            'title' => 'Receipt Bike',
            'quantity' => 1,
            'line_total' => 1000000,
        ]);
    }

    public function test_receipt_admin_and_print_pages_render(): void
    {
        $user = User::factory()->create();
        [$order] = $this->createOrderWithItem();
        $receipt = app(ReceiptGenerator::class)->forOrder($order);

        $paths = [
            '/admin/receipts',
            "/admin/receipts/{$receipt->id}",
            "/admin/receipts/{$receipt->id}/edit",
            "/admin/receipts/{$receipt->id}/print",
        ];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->get($path)
                ->assertOk()
                ->assertSee($receipt->receipt_number);
        }

        $this->actingAs($user)
            ->get('/admin/receipts/create')
            ->assertOk();

        $this->actingAs($user)
            ->get("/admin/receipts/{$receipt->id}/print")
            ->assertSee('Print / Save PDF')
            ->assertSee('Receipt Bike');
    }

    /**
     * @return array{Order, Product}
     */
    private function createOrderWithItem(): array
    {
        $category = ProductCategory::query()->create([
            'slug' => 'bikes',
            'label' => 'Bikes',
        ]);

        $product = Product::query()->create([
            'product_category_id' => $category->id,
            'slug' => 'receipt-bike',
            'sku' => 'RECEIPT-BIKE',
            'title' => 'Receipt Bike',
            'subtitle' => 'Receipt product',
            'availability' => 'in_stock',
            'price_value' => 1000000,
            'stock_quantity' => 10,
        ]);

        $order = Order::query()->create([
            'customer_name' => 'Receipt Customer',
            'customer_email' => 'receipt@example.com',
            'customer_phone' => '+989120000000',
            'status' => 'pending',
            'payment_status' => 'paid',
            'fulfillment_method' => 'pickup',
        ]);

        $order->items()->create([
            'product_id' => $product->slug,
            'title' => $product->title,
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => $product->price_value,
            'metadata' => [
                'product_database_id' => $product->id,
            ],
        ]);

        return [$order->fresh('items'), $product];
    }
}
