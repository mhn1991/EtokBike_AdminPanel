<?php

namespace App\Support\Mobile;

use App\Models\Order;
use App\Models\Product;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramGalleryItem;
use App\Models\ServiceBooking;
use App\Models\StoreProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HomeScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback, ?\App\Models\User $user = null): array
    {
        if (! static::canUseAnyDatabase()) {
            return $fallback;
        }

        $featuredProducts = static::featuredProducts();
        $programCategories = static::programCategories();
        $statusItems = static::statusItems($user);
        $storeProfile = static::storeProfile();

        if ($featuredProducts->isEmpty() && $programCategories->isEmpty() && empty($statusItems) && ! $storeProfile) {
            return $fallback;
        }

        $screen = $fallback;
        $screen['version'] = static::version($fallback);

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) === 'featured-bikes' && $featuredProducts->isNotEmpty()) {
                $section['data']['items'] = $featuredProducts
                    ->map(fn (Product $product): array => $product->toMobilePayload())
                    ->values()
                    ->all();
            }

            if (($section['id'] ?? null) === 'weekly-programs' && $programCategories->isNotEmpty()) {
                $section['data']['defaultSubsection'] = $programCategories->first()->slug;
                $section['data']['subsections'] = $programCategories
                    ->map(fn (ProgramCategory $category): array => [
                        'id' => $category->slug,
                        'label' => $category->label,
                        'title' => $category->title,
                        'items' => $category->programs
                            ->map(fn (Program $program): array => static::programOfferPayload($program))
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all();
            }

            if (($section['id'] ?? null) === 'customer-status') {
                $section['data']['items'] = $statusItems;
            }

            if (($section['id'] ?? null) === 'store-status' && $storeProfile) {
                $section['data']['items'] = [$storeProfile->statusMobilePayload()];
            }

            if (($section['id'] ?? null) === 'store-info' && $storeProfile) {
                $section['data']['items'] = [$storeProfile->infoMobilePayload()];
            }
        }

        return $screen;
    }

    public static function version(array $fallback): int
    {
        $timestamps = [];

        if (static::hasTables(['product_categories', 'products'])) {
            $timestamps[] = Product::query()->max('updated_at');
        }

        if (static::hasTables(['program_categories', 'programs', 'program_gallery_items'])) {
            $timestamps[] = ProgramCategory::query()->max('updated_at');
            $timestamps[] = Program::query()->max('updated_at');
            $timestamps[] = ProgramGalleryItem::query()->max('updated_at');
        }

        if (static::hasTables(['orders', 'order_items'])) {
            $timestamps[] = Order::query()->max('updated_at');
        }

        if (static::hasTables(['service_bookings'])) {
            $timestamps[] = ServiceBooking::query()->max('updated_at');
        }

        if (static::hasTables(['store_profiles'])) {
            $timestamps[] = StoreProfile::query()->max('updated_at');
        }

        $timestamp = collect($timestamps)
            ->filter()
            ->map(fn ($value): int => strtotime((string) $value) ?: 0)
            ->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseAnyDatabase(): bool
    {
        return static::hasTables(['product_categories', 'products'])
            || static::hasTables(['program_categories', 'programs', 'program_gallery_items'])
            || static::hasTables(['orders', 'order_items'])
            || static::hasTables(['service_bookings'])
            || static::hasTables(['store_profiles']);
    }

    private static function storeProfile(): ?StoreProfile
    {
        if (! static::hasTables(['store_profiles'])) {
            return null;
        }

        return StoreProfile::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();
    }

    /**
     * @return Collection<int, Product>
     */
    private static function featuredProducts(): Collection
    {
        if (! static::hasTables(['product_categories', 'products'])) {
            return collect();
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get();

        if ($products->isNotEmpty()) {
            return $products;
        }

        return Product::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->limit(6)
            ->get();
    }

    /**
     * @return Collection<int, ProgramCategory>
     */
    private static function programCategories(): Collection
    {
        if (! static::hasTables(['program_categories', 'programs', 'program_gallery_items'])) {
            return collect();
        }

        return ProgramCategory::query()
            ->where('is_active', true)
            ->with([
                'programs' => fn ($query) => $query
                    ->where('is_active', true)
                    ->where('program_state', 'future')
                    ->orderBy('sort_order')
                    ->orderBy('date_value'),
            ])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->filter(fn (ProgramCategory $category): bool => $category->programs->isNotEmpty())
            ->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function statusItems(?\App\Models\User $user = null): array
    {
        $items = collect();

        if (static::hasTables(['orders', 'order_items'])) {
            $orders = Order::query()
                ->with('items')
                ->when($user, fn ($query) => $query->where('user_id', $user->id))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest('updated_at')
                ->limit(2)
                ->get();

            $items = $items->merge($orders->map(fn (Order $order): array => static::orderStatusPayload($order)));
        }

        if (static::hasTables(['service_bookings'])) {
            $bookings = ServiceBooking::query()
                ->when($user, fn ($query) => $query->where('user_id', $user->id))
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->latest('updated_at')
                ->limit(2)
                ->get();

            $items = $items->merge($bookings->map(fn (ServiceBooking $booking): array => static::bookingStatusPayload($booking)));
        }

        return $items->take(3)->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function programOfferPayload(Program $program): array
    {
        return [
            'title' => $program->title,
            'subtitle' => $program->date_label ?: $program->subtitle,
            'description' => $program->advertisement ?: $program->subtitle,
            'price' => $program->book_label ?: 'رزرو برنامه',
            'thumbnailText' => $program->thumbnail_text,
            'thumbnailColor' => $program->thumbnail_color,
            'imageUrl' => ImageUrl::resolve($program->image_url),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function orderStatusPayload(Order $order): array
    {
        return [
            'id' => 'order-'.$order->id,
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
            'id' => 'service-booking-'.$booking->id,
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

    private static function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'confirmed' => 'سفارش تایید شده',
            'processing' => 'در حال آماده‌سازی',
            'ready' => 'آماده تحویل',
            default => 'در انتظار بررسی',
        };
    }

    private static function bookingStatusLabel(string $status): string
    {
        return match ($status) {
            'confirmed' => 'زمان سرویس تایید شده',
            'in_progress' => 'سرویس در حال انجام',
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
