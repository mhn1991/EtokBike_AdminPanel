<?php

namespace App\Support\Mobile;

use App\Models\ServiceBooking;
use App\Models\ServiceCategory;
use App\Models\ServiceOffering;
use App\Models\ServiceTimeSlot;
use Illuminate\Support\Facades\Schema;

class ServicesScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $categories = ServiceCategory::query()
            ->where('is_active', true)
            ->with(['offerings' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('title')])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        if ($categories->isEmpty()) {
            return $fallback;
        }

        $screen = $fallback;
        $screen['version'] = static::version($fallback);
        $activeBookings = static::activeBookings();
        $serviceTitles = $categories
            ->flatMap(fn (ServiceCategory $category) => $category->offerings->pluck('title'))
            ->unique()
            ->values()
            ->all();
        $bikeLabels = static::bikeLabels();

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) === 'service-booking') {
                $section['data']['defaultSubsection'] = $categories->first()->slug;
                $section['data']['subsections'] = $categories
                    ->map(fn (ServiceCategory $category): array => [
                        'id' => $category->slug,
                        'label' => $category->label,
                        'title' => $category->title,
                        'items' => $category->offerings
                            ->map(fn ($offering): array => $offering->toMobilePayload())
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all();
            }

            if (($section['id'] ?? null) === 'booking-form') {
                $section['data']['services'] = $serviceTitles;
                $section['data']['bikes'] = $bikeLabels;
                $section['data']['timeSlots'] = static::timeSlots($section['data']['timeSlots'] ?? []);
            }

            if (($section['id'] ?? null) === 'repair-status') {
                $section['data']['items'] = $activeBookings
                    ->map(fn (ServiceBooking $booking): array => static::bookingStatusPayload($booking))
                    ->values()
                    ->all();
            }
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        if (! static::canUseDatabase()) {
            return (int) ($fallback['version'] ?? 1);
        }

        $timestamp = collect([
            ServiceCategory::query()->max('updated_at'),
            ServiceOffering::query()->max('updated_at'),
            static::hasTable('service_bookings') ? ServiceBooking::query()->max('updated_at') : null,
            static::hasTable('service_time_slots') ? ServiceTimeSlot::query()->max('updated_at') : null,
        ])->filter()->map(fn ($value): int => strtotime((string) $value) ?: 0)->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('service_categories')
            && Schema::hasTable('service_offerings');
    }

    private static function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private static function activeBookings()
    {
        if (! static::hasTable('service_bookings')) {
            return collect();
        }

        return ServiceBooking::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('updated_at')
            ->limit(3)
            ->get();
    }

    /**
     * @return array<int, string>
     */
    private static function bikeLabels(): array
    {
        if (! static::hasTable('service_bookings')) {
            return ['ثبت دوچرخه جدید'];
        }

        $labels = ServiceBooking::query()
            ->whereNotNull('bike_label')
            ->latest('updated_at')
            ->pluck('bike_label')
            ->unique()
            ->take(5)
            ->values()
            ->all();

        $labels[] = 'ثبت دوچرخه جدید';

        return array_values(array_unique($labels));
    }

    /**
     * @param  array<int, string>  $fallback
     * @return array<int, string>
     */
    private static function timeSlots(array $fallback): array
    {
        if (! static::hasTable('service_time_slots')) {
            return $fallback;
        }

        $slots = ServiceTimeSlot::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->pluck('label')
            ->values()
            ->all();

        return empty($slots) ? $fallback : $slots;
    }

    /**
     * @return array<string, mixed>
     */
    private static function bookingStatusPayload(ServiceBooking $booking): array
    {
        return [
            'id' => 'service-'.$booking->id,
            'title' => $booking->service_type,
            'subtitle' => $booking->bike_label ?: $booking->customer_name,
            'status' => static::bookingStatusLabel($booking->status),
            'currentLocation' => 'وضعیت فعلی: تعمیرگاه EtokBike',
            'expandLabel' => 'مشاهده روند تعمیر',
            'collapseLabel' => 'بستن روند تعمیر',
            'currentStep' => static::bookingCurrentStep($booking->status),
            'steps' => [
                ['label' => 'ثبت درخواست', 'detail' => 'درخواست سرویس دریافت شد.'],
                ['label' => 'تایید زمان', 'detail' => 'زمان مراجعه یا دریافت دوچرخه هماهنگ می‌شود.'],
                ['label' => 'در حال انجام', 'detail' => 'دوچرخه در تعمیرگاه بررسی و سرویس می‌شود.'],
                ['label' => 'آماده تحویل', 'detail' => 'پس از پایان سرویس، تحویل هماهنگ می‌شود.'],
            ],
        ];
    }

    private static function bookingStatusLabel(string $status): string
    {
        return match ($status) {
            'confirmed' => 'زمان سرویس تایید شده',
            'in_progress' => 'سرویس در حال انجام',
            default => 'در انتظار تایید',
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
}
