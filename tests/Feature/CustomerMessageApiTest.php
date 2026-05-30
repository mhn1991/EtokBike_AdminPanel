<?php

namespace Tests\Feature;

use App\Models\MessageDepartment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMessageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_customer_message_from_the_api(): void
    {
        MessageDepartment::query()->create([
            'slug' => 'support',
            'title' => 'پشتیبانی سفارش',
            'thread_title' => 'گفتگو با پشتیبانی سفارش',
            'composer_title' => 'ارسال پیام به پشتیبانی سفارش',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/messages', [
            'department' => 'support',
            'label' => 'مشتری اپ',
            'text' => 'سلام، وضعیت سفارش من چیست؟',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.department', 'support')
            ->assertJsonPath('data.sender', 'client')
            ->assertJsonPath('data.is_unread', true);

        $this->assertDatabaseHas('customer_messages', [
            'sender' => 'client',
            'label' => 'مشتری اپ',
            'text' => 'سلام، وضعیت سفارش من چیست؟',
            'is_unread' => true,
        ]);
    }

    public function test_it_rejects_unknown_message_departments(): void
    {
        $this->postJson('/api/messages', [
            'department' => 'unknown',
            'text' => 'پیام تست',
        ])->assertUnprocessable();
    }
}
