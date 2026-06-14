<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerMessage;
use App\Models\MessageDepartment;
use App\Support\Customers\CustomerProfileUpdater;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(): View
    {
        return view('storefront.messages.index', [
            'departments' => MessageDepartment::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->get(),
            'meta' => [
                'title' => Seo::defaultTitle('پیام به EtokBike'),
                'description' => Seo::defaultDescription('ارسال پیام به فروش، خدمات یا پشتیبانی سفارش EtokBike.'),
                'canonical' => route('storefront.messages'),
                'robots' => 'noindex,follow',
            ],
        ]);
    }

    public function store(Request $request, CustomerProfileUpdater $profiles): RedirectResponse
    {
        $validated = $request->validate([
            'message_department_id' => ['required', 'integer', 'exists:message_departments,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'text' => ['required', 'string', 'max:3000'],
        ]);

        $department = MessageDepartment::query()
            ->where('is_active', true)
            ->findOrFail($validated['message_department_id']);

        $profile = $profiles->update(null, $validated);

        CustomerMessage::query()->create([
            'message_department_id' => $department->id,
            'user_id' => $profile?->user_id,
            'sender' => 'client',
            'label' => trim($validated['customer_name'].' - '.$validated['customer_phone']),
            'text' => $this->messageText($validated),
            'time_label' => 'اکنون',
            'is_unread' => true,
        ]);

        return redirect()
            ->route('storefront.messages')
            ->with('status', 'پیام شما ثبت شد و در پنل پشتیبانی قرار گرفت.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function messageText(array $validated): string
    {
        $parts = [
            'پیام: '.$validated['text'],
        ];

        if (! blank($validated['customer_email'] ?? null)) {
            $parts[] = 'ایمیل: '.$validated['customer_email'];
        }

        return implode("\n", $parts);
    }
}
