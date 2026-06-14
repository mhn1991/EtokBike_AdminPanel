<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\ServiceBooking;
use App\Models\ServiceCategory;
use App\Models\ServiceTimeSlot;
use App\Models\StoreProfile;
use App\Support\Customers\CustomerProfileUpdater;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(): View
    {
        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->with(['offerings' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return view('storefront.services.index', [
            'categories' => $categories,
            'timeSlots' => ServiceTimeSlot::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
            'storeProfile' => StoreProfile::query()->where('is_active', true)->first(),
            'meta' => [
                'title' => Seo::defaultTitle('خدمات دوچرخه | EtokBike'),
                'description' => Seo::defaultDescription('رزرو سرویس، تعمیر، تنظیم و عیب‌یابی دوچرخه در تعمیرگاه EtokBike.'),
                'canonical' => route('storefront.services'),
            ],
            'structuredData' => [
                Seo::breadcrumbs([
                    ['name' => Seo::siteName(), 'url' => route('storefront.home')],
                    ['name' => 'خدمات', 'url' => route('storefront.services')],
                ]),
            ],
        ]);
    }

    public function store(Request $request, CustomerProfileUpdater $profiles): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'service_type' => ['required', 'string', 'max:255'],
            'bike_label' => ['nullable', 'string', 'max:255'],
            'preferred_time' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $profiles->update(null, $validated);

        ServiceBooking::query()->create([
            ...$validated,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('storefront.services')
            ->with('status', 'درخواست سرویس ثبت شد. تیم EtokBike برای هماهنگی تماس می‌گیرد.');
    }
}
