<?php

namespace App\Support\Mobile;

use App\Models\Order;
use App\Models\ServiceBooking;
use Illuminate\Support\Facades\Schema;

class AccountScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $latestOrder = static::latestOrder();
        $latestBooking = static::latestBooking();

        if (! $latestOrder && ! $latestBooking) {
            return $fallback;
        }

        $screen = $fallback;
        $screen['version'] = static::version($fallback);
        $customer = static::customerFrom($latestOrder, $latestBooking);
        $statusItems = static::statusItems();
        $historyItems = static::historyItems();
        $bikeProfiles = static::bikeProfiles();

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) === 'profile-summary') {
                $section['data']['title'] = 'سلام، '.$customer['name'];
                $section['data']['subtitle'] = 'سفارش‌ها، سرویس‌ها و اطلاعات تماس شما از پنل EtokBike به‌روز می‌شود.';
            }

            if (($section['id'] ?? null) === 'ongoing-purchases') {
                $section['data']['items'] = $statusItems;
            }

            if (($section['id'] ?? null) === 'client-details') {
                $section['data']['fields'] = static::customerFields($customer);
            }

            if (($section['id'] ?? null) === 'purchase-history' && ! empty($historyItems)) {
                $section['data']['items'] = $historyItems;
            }

            if (($section['id'] ?? null) === 'bike-profiles' && ! empty($bikeProfiles)) {
                $section['data']['items'] = $bikeProfiles;
            }
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        $timestamps = [];

        if (static::hasTables(['orders', 'order_items'])) {
            $timestamps[] = Order::query()->max('updated_at');
        }

        if (static::hasTables(['service_bookings'])) {
            $timestamps[] = ServiceBooking::query()->max('updated_at');
        }

        $timestamp = collect($timestamps)
            ->filter()
            ->map(fn ($value): int => strtotime((string) $value) ?: 0)
            ->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return static::hasTables(['orders', 'order_items']) || static::hasTables(['service_bookings']);
    }

    private static function latestOrder(): ?Order
    {
        if (! static::hasTables(['orders', 'order_items'])) {
            return null;
        }

        return Order::query()
            ->with('items')
            ->latest('updated_at')
            ->first();
    }

    private static function latestBooking(): ?ServiceBooking
    {
        if (! static::hasTables(['service_bookings'])) {
            return null;
        }

        return ServiceBooking::query()
            ->latest('updated_at')
            ->first();
    }

    /**
     * @return array{name: string, phone: ?string, email: ?string}
     */
    private static function customerFrom(?Order $order, ?ServiceBooking $booking): array
    {
        $orderTimestamp = $order?->updated_at?->getTimestamp() ?? 0;
        $bookingTimestamp = $booking?->updated_at?->getTimestamp() ?? 0;

        if ($booking && $bookingTimestamp > $orderTimestamp) {
            return [
                'name' => $booking->customer_name,
                'phone' => $booking->customer_phone,
                'email' => $booking->customer_email,
            ];
        }

        if ($order) {
            return [
                'name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email,
            ];
        }

        return [
            'name' => $booking?->customer_name ?: 'مشتری EtokBike',
            'phone' => $booking?->customer_phone,
            'email' => $booking?->customer_email,
        ];
    }

    /**
     * @param  array{name: string, phone: ?string, email: ?string}  $customer
     * @return array<int, array<string, string>>
     */
    private static function customerFields(array $customer): array
    {
        return [
            ['label' => 'نام کامل', 'value' => $customer['name']],
            ['label' => 'شماره تماس', 'value' => $customer['phone'] ?: 'ثبت نشده'],
            ['label' => 'ایمیل', 'value' => $customer['email'] ?: 'ثبت نشده'],
            ['label' => 'منبع اطلاعات', 'value' => 'پنل مدیریت EtokBike'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function statusItems(): array
    {
        $items = collect();

        if (static::hasTables(['orders', 'order_items'])) {
            $items = $items->merge(Order::query()
                ->with('items')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest('updated_at')
                ->limit(3)
                ->get()
                ->map(fn (Order $order): array => static::orderStatusPayload($order)));
        }

        if (static::hasTables(['service_bookings'])) {
            $items = $items->merge(ServiceBooking::query()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest('updated_at')
                ->limit(3)
                ->get()
                ->map(fn (ServiceBooking $booking): array => static::bookingStatusPayload($booking)));
        }

        return $items->take(4)->values()->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function historyItems(): array
    {
        $items = collect();

        if (static::hasTables(['orders', 'order_items'])) {
            $items = $items->merge(Order::query()
                ->with('items')
                ->whereIn('status', ['completed', 'cancelled'])
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Order $order): array => static::orderHistoryPayload($order)));
        }

        if (static::hasTables(['service_bookings'])) {
            $items = $items->merge(ServiceBooking::query()
                ->whereIn('status', ['completed', 'cancelled'])
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (ServiceBooking $booking): array => static::bookingHistoryPayload($booking)));
        }

        return $items->take(5)->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function bikeProfiles(): array
    {
        if (! static::hasTables(['service_bookings'])) {
            return [];
        }

        return ServiceBooking::query()
            ->whereNotNull('bike_label')
            ->latest('updated_at')
            ->get()
            ->unique('bike_label')
            ->take(3)
            ->map(fn (ServiceBooking $booking): array => [
                'title' => $booking->bike_label,
                'subtitle' => 'دوچرخه ثبت‌شده در خدمات',
                'fields' => [
                    ['label' => 'آخرین درخواست', 'value' => $booking->service_type],
                    ['label' => 'زمان پیشنهادی', 'value' => $booking->preferred_time ?: 'ثبت نشده'],
                    ['label' => 'وضعیت', 'value' => static::bookingStatusLabel($booking->status)],
                ],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function orderStatusPayload(Order $order): array
    {
        return [
            'id' => 'account-order-'.$order->id,
            'title' => 'سفارش '.$order->order_number,
            'subtitle' => $order->items->pluck('title')->take(2)->implode(' + ') ?: 'سفارش فروشگاه',
            'status' => static::orderStatusLabel($order->status),
            'currentLocation' => 'وضعیت فعلی: فروشگاه EtokBike',
            'expandLabel' => 'مشاهده روند سفارش',
            'collapseLabel' => 'بستن روند سفارش',
            'currentStep' => static::orderCurrentStep($order->status),
            'steps' => [
                ['label' => 'ثبت سفارش', 'detail' => 'سفارش ثبت و برای بررسی ارسال شد.'],
                ['label' => 'آماده‌سازی', 'detail' => 'محصول در فروشگاه بررسی، تنظیم و بسته‌بندی می‌شود.'],
                ['label' => 'ارسال یا تحویل', 'detail' => 'پس از آماده شدن، هماهنگی تحویل انجام می‌شود.'],
                ['label' => 'تکمیل', 'detail' => 'پس از دریافت سفارش توسط مشتری تکمیل می‌شود.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function bookingStatusPayload(ServiceBooking $booking): array
    {
        return [
            'id' => 'account-service-'.$booking->id,
            'title' => $booking->service_type,
            'subtitle' => $booking->bike_label ?: $booking->customer_name,
            'status' => static::bookingStatusLabel($booking->status),
            'currentLocation' => 'وضعیت فعلی: تعمیرگاه EtokBike',
            'expandLabel' => 'مشاهده روند سرویس',
            'collapseLabel' => 'بستن روند سرویس',
            'currentStep' => static::bookingCurrentStep($booking->status),
            'steps' => [
                ['label' => 'ثبت درخواست', 'detail' => 'درخواست سرویس دریافت شد.'],
                ['label' => 'تایید زمان', 'detail' => 'زمان مراجعه یا دریافت دوچرخه هماهنگ می‌شود.'],
                ['label' => 'در حال انجام', 'detail' => 'دوچرخه در تعمیرگاه بررسی و سرویس می‌شود.'],
                ['label' => 'آماده تحویل', 'detail' => 'پس از پایان سرویس، تحویل هماهنگ می‌شود.'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function orderHistoryPayload(Order $order): array
    {
        return [
            'title' => 'سفارش '.$order->order_number,
            'subtitle' => $order->items->pluck('title')->take(2)->implode(' + ') ?: 'سفارش فروشگاه',
            'description' => static::orderStatusLabel($order->status).' - '.($order->placed_at?->format('Y/m/d') ?: $order->updated_at?->format('Y/m/d')),
            'price' => static::formatToman($order->total),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function bookingHistoryPayload(ServiceBooking $booking): array
    {
        return [
            'title' => $booking->service_type,
            'subtitle' => $booking->bike_label ?: $booking->customer_name,
            'description' => static::bookingStatusLabel($booking->status).' - '.($booking->updated_at?->format('Y/m/d') ?: ''),
            'price' => static::bookingStatusLabel($booking->status),
        ];
    }

    private static function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'confirmed' => 'سفارش تایید شده',
            'processing' => 'در حال آماده‌سازی',
            'ready' => 'آماده تحویل',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            default => 'در انتظار بررسی',
        };
    }

    private static function bookingStatusLabel(string $status): string
    {
        return match ($status) {
            'confirmed' => 'زمان سرویس تایید شده',
            'in_progress' => 'سرویس در حال انجام',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
            default => 'در انتظار تایید',
        };
    }

    private static function orderCurrentStep(string $status): int
    {
        return match ($status) {
            'processing' => 1,
            'ready' => 2,
            'completed' => 3,
            default => 0,
        };
    }

    private static function bookingCurrentStep(string $status): int
    {
        return match ($status) {
            'confirmed' => 1,
            'in_progress' => 2,
            'completed' => 3,
            default => 0,
        };
    }

    private static function formatToman(?int $value): string
    {
        return number_format($value ?? 0).' تومان';
    }

    /**
     * @param  array<int, string>  $tables
     */
    private static function hasTables(array $tables): bool
    {
        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }
}
