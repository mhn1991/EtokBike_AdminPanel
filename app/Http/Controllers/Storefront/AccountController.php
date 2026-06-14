<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerProfile;
use App\Models\Order;
use App\Models\ProgramBooking;
use App\Models\ServiceBooking;
use App\Support\Storefront\Seo;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $lookup = $request->validate([
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'order_number' => ['nullable', 'string', 'max:255'],
        ]);

        $phone = trim((string) ($lookup['phone'] ?? ''));
        $email = trim((string) ($lookup['email'] ?? ''));
        $orderNumber = trim((string) ($lookup['order_number'] ?? ''));
        $hasLookup = $phone !== '' || $email !== '' || $orderNumber !== '';

        if ($orderNumber !== '') {
            $seedOrder = Order::query()
                ->where('order_number', $orderNumber)
                ->first();

            $phone = $phone !== '' ? $phone : (string) ($seedOrder?->customer_phone ?? '');
            $email = $email !== '' ? $email : (string) ($seedOrder?->customer_email ?? '');
        }

        return view('storefront.account.show', [
            'lookup' => [
                'phone' => $phone,
                'email' => $email,
                'order_number' => $orderNumber,
            ],
            'hasLookup' => $hasLookup,
            'profile' => $hasLookup ? $this->profile($phone, $email) : null,
            'orders' => $hasLookup ? $this->orders($phone, $email, $orderNumber) : collect(),
            'serviceBookings' => $hasLookup ? $this->serviceBookings($phone, $email) : collect(),
            'programBookings' => $hasLookup ? $this->programBookings($phone, $email) : collect(),
            'meta' => [
                'title' => Seo::defaultTitle('پیگیری حساب | EtokBike'),
                'description' => Seo::defaultDescription('پیگیری سفارش‌ها، سرویس‌ها، برنامه‌ها و مشخصات دوچرخه در EtokBike.'),
                'canonical' => route('storefront.account'),
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }

    private function profile(string $phone, string $email): ?CustomerProfile
    {
        if ($phone === '' && $email === '') {
            return null;
        }

        return CustomerProfile::query()
            ->with(['bikeProfiles' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')])
            ->where('is_active', true)
            ->where(function (Builder $query) use ($phone, $email): void {
                if ($phone !== '') {
                    $query->orWhere('phone', $phone);
                }

                if ($email !== '') {
                    $query->orWhere('email', $email);
                }
            })
            ->first();
    }

    private function orders(string $phone, string $email, string $orderNumber)
    {
        return Order::query()
            ->with(['items', 'shipments.deliveryZone', 'receipts', 'returnRequests'])
            ->where(function (Builder $query) use ($phone, $email, $orderNumber): void {
                if ($orderNumber !== '') {
                    $query->orWhere('order_number', $orderNumber);
                }

                if ($phone !== '') {
                    $query->orWhere('customer_phone', $phone);
                }

                if ($email !== '') {
                    $query->orWhere('customer_email', $email);
                }
            })
            ->latest('updated_at')
            ->limit(12)
            ->get();
    }

    private function serviceBookings(string $phone, string $email)
    {
        if ($phone === '' && $email === '') {
            return collect();
        }

        return ServiceBooking::query()
            ->where(function (Builder $query) use ($phone, $email): void {
                if ($phone !== '') {
                    $query->orWhere('customer_phone', $phone);
                }

                if ($email !== '') {
                    $query->orWhere('customer_email', $email);
                }
            })
            ->latest('updated_at')
            ->limit(12)
            ->get();
    }

    private function programBookings(string $phone, string $email)
    {
        if ($phone === '' && $email === '') {
            return collect();
        }

        return ProgramBooking::query()
            ->with('program')
            ->where(function (Builder $query) use ($phone, $email): void {
                if ($phone !== '') {
                    $query->orWhere('customer_phone', $phone);
                }

                if ($email !== '') {
                    $query->orWhere('customer_email', $email);
                }
            })
            ->latest('updated_at')
            ->limit(12)
            ->get();
    }
}
