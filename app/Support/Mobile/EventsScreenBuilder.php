<?php

namespace App\Support\Mobile;

use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ProgramGalleryItem;
use Illuminate\Support\Facades\Schema;

class EventsScreenBuilder
{
    /**
     * @return array<string, mixed>
     */
    public static function build(array $fallback, ?\App\Models\User $user = null): array
    {
        if (! static::canUseDatabase()) {
            return $fallback;
        }

        $categories = ProgramCategory::query()
            ->where('is_active', true)
            ->with([
                'programs' => fn ($query) => $query
                    ->where('is_active', true)
                    ->with('galleryItems')
                    ->orderBy('sort_order')
                    ->orderBy('date_value'),
            ])
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        if ($categories->isEmpty()) {
            return $fallback;
        }

        $screen = $fallback;
        $screen['version'] = static::version($fallback);

        foreach ($screen['sections'] as &$section) {
            if (($section['id'] ?? null) !== 'events-list') {
                continue;
            }

            $section['data']['defaultSubsection'] = $categories->first()->slug;
            $section['data']['subsections'] = $categories
                ->map(fn (ProgramCategory $category): array => [
                    'id' => $category->slug,
                    'label' => $category->label,
                    'title' => $category->title,
                    'items' => $category->programs
                        ->map(fn ($program): array => $program->toMobilePayload())
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

        $categoryVersion = ProgramCategory::query()->max('updated_at');
        $programVersion = Program::query()->max('updated_at');
        $galleryVersion = ProgramGalleryItem::query()->max('updated_at');
        $timestamp = collect([$categoryVersion, $programVersion, $galleryVersion])
            ->filter()
            ->map(fn ($value): int => strtotime((string) $value) ?: 0)
            ->max();

        return max((int) ($fallback['version'] ?? 1), $timestamp ?: 0);
    }

    private static function canUseDatabase(): bool
    {
        return Schema::hasTable('program_categories')
            && Schema::hasTable('programs')
            && Schema::hasTable('program_gallery_items');
    }
}
