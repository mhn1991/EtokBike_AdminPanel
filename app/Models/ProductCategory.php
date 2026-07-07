<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'parent_id',
    'slug',
    'label',
    'seo_title',
    'seo_description',
    'canonical_url',
    'robots',
    'og_title',
    'og_description',
    'og_image',
    'include_in_sitemap',
    'sitemap_priority',
    'sitemap_change_frequency',
    'sort_order',
    'is_active',
])]
class ProductCategory extends Model
{
    public const ROBOTS_OPTIONS = [
        'index,follow' => 'Index, follow',
        'noindex,follow' => 'No index, follow',
        'noindex,nofollow' => 'No index, no follow',
    ];

    public const CHANGE_FREQUENCY_OPTIONS = [
        'always' => 'Always',
        'hourly' => 'Hourly',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'never' => 'Never',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label');
    }

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductCategory $category): void {
            $category->ensureParentIsNotSelfOrDescendant();
        });
    }

    /**
     * @return array<int, string>
     */
    public static function formOptions(?self $excludedCategory = null): array
    {
        $excludedIds = $excludedCategory
            ? [$excludedCategory->getKey(), ...$excludedCategory->descendantIds()]
            : [];

        return self::flattenTree(
            self::query()
                ->when($excludedIds !== [], fn ($query) => $query->whereNotIn('id', $excludedIds))
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
            includeProductCounts: false,
        )
            ->mapWithKeys(fn (ProductCategory $category): array => [
                $category->id => $category->pathLabel(),
            ])
            ->all();
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public static function activeTreeForStorefront(): Collection
    {
        return self::flattenTree(
            self::query()
                ->where('is_active', true)
                ->withCount([
                    'products as direct_active_products_count' => fn ($query) => $query->where('is_active', true),
                ])
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
        );
    }

    /**
     * @return array<int>
     */
    public function descendantAndSelfIds(): array
    {
        return [$this->getKey(), ...$this->descendantIds()];
    }

    /**
     * @return array<int>
     */
    public function descendantIds(): array
    {
        $categories = self::query()
            ->select(['id', 'parent_id'])
            ->get();

        return self::descendantIdsFromCollection($categories, $this->getKey());
    }

    public function pathLabel(string $separator = ' / '): string
    {
        return $this->breadcrumbCategories()
            ->pluck('label')
            ->join($separator);
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function breadcrumbCategories(): Collection
    {
        $categories = collect();
        $category = $this;

        while ($category) {
            $categories->prepend($category);
            $category = $category->parent;
        }

        return $categories->values();
    }

    private function ensureParentIsNotSelfOrDescendant(): void
    {
        if (blank($this->parent_id) || ! $this->exists) {
            return;
        }

        $ancestorId = (int) $this->parent_id;

        while ($ancestorId > 0) {
            if ($ancestorId === (int) $this->getKey()) {
                throw ValidationException::withMessages([
                    'parent_id' => __('A category cannot use itself or one of its subcategories as parent.'),
                ]);
            }

            $ancestorId = (int) (self::query()->whereKey($ancestorId)->value('parent_id') ?? 0);
        }
    }

    /**
     * @param  Collection<int, ProductCategory>  $categories
     * @return Collection<int, ProductCategory>
     */
    private static function flattenTree(Collection $categories, bool $includeProductCounts = true): Collection
    {
        $childrenByParent = $categories
            ->sortBy([
                ['sort_order', 'asc'],
                ['label', 'asc'],
            ])
            ->groupBy(fn (ProductCategory $category): int => (int) ($category->parent_id ?? 0));

        $aggregateCounts = [];

        $aggregateCount = function (ProductCategory $category) use (&$aggregateCount, &$aggregateCounts, $childrenByParent): int {
            if (array_key_exists($category->id, $aggregateCounts)) {
                return $aggregateCounts[$category->id];
            }

            $count = (int) ($category->direct_active_products_count ?? 0);

            foreach ($childrenByParent->get($category->id, collect()) as $child) {
                $count += $aggregateCount($child);
            }

            return $aggregateCounts[$category->id] = $count;
        };

        $flatten = function (int $parentId, int $depth, string $prefix = '') use (&$flatten, $childrenByParent, $includeProductCounts, $aggregateCount): Collection {
            return $childrenByParent
                ->get($parentId, collect())
                ->flatMap(function (ProductCategory $category) use ($depth, $prefix, $flatten, $includeProductCounts, $aggregateCount): Collection {
                    $category->setAttribute('tree_depth', $depth);
                    $category->setAttribute('tree_label', trim($prefix.$category->label));

                    if ($includeProductCounts) {
                        $category->setAttribute('active_products_count', $aggregateCount($category));
                    }

                    return collect([$category])
                        ->merge($flatten($category->id, $depth + 1, $prefix.'— '));
                });
        };

        $tree = $flatten(0, 0);
        $orphanRoots = $categories
            ->filter(fn (ProductCategory $category): bool => filled($category->parent_id) && ! $categories->contains('id', $category->parent_id))
            ->values();

        if ($orphanRoots->isEmpty()) {
            return $tree->values();
        }

        return $tree
            ->merge($orphanRoots->flatMap(function (ProductCategory $category) use (&$flatten, $includeProductCounts, $aggregateCount): Collection {
                $category->setAttribute('tree_depth', 0);
                $category->setAttribute('tree_label', $category->label);

                if ($includeProductCounts) {
                    $category->setAttribute('active_products_count', $aggregateCount($category));
                }

                return collect([$category])
                    ->merge($flatten($category->id, 1, '— '));
            }))
            ->unique('id')
            ->values();
    }

    /**
     * @param  Collection<int, ProductCategory>  $categories
     * @return array<int>
     */
    private static function descendantIdsFromCollection(Collection $categories, int $parentId): array
    {
        return $categories
            ->where('parent_id', $parentId)
            ->flatMap(fn (ProductCategory $category): array => [
                $category->id,
                ...self::descendantIdsFromCollection($categories, $category->id),
            ])
            ->values()
            ->all();
    }
}
