<?php

namespace App\Support\Mobile;

use App\Models\ServiceCategory;
use App\Models\ServiceOffering;
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

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) !== 'service-booking') {
                continue;
            }

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
        ])->filter()->map(fn ($value): int => strtotime((string) $value) ?: 0)->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('service_categories')
            && Schema::hasTable('service_offerings');
    }
}
