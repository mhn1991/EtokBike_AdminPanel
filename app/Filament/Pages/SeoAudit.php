<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SeoAudit extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'ممیزی SEO';

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'ممیزی SEO';

    protected string $view = 'filament.pages.seo-audit';

    /**
     * Only genuinely actionable gaps are reported here. A blank meta title or
     * description is NOT one of them: the storefront already builds a working
     * fallback from the title, subtitle, description, or excerpt automatically
     * (see App\Support\Storefront\Seo and PageController), so an empty field is
     * a supported, automatic state rather than a defect worth an admin's time.
     *
     * @return array<string, array{value: int, severity: string, hint: string}>
     */
    public function metrics(): array
    {
        return [
            __('Products with nothing to show search engines') => $this->metric(
                $this->productsMissingFallbackContent(),
                'critical',
                __('Neither a meta description nor a subtitle exists, so the automatic fallback has nothing to use. Add a subtitle or a meta description.'),
            ),
            __('Products sharing a duplicate search title') => $this->metric(
                $this->duplicateTitleCount(Product::query()->where('is_active', true), 'seo_title', 'title'),
                'critical',
                __('Two or more visible products resolve to the exact same search title. Search engines may drop one of them from results. Set a distinct meta title on each.'),
            ),
            __('Categories sharing a duplicate search title') => $this->metric(
                $this->duplicateTitleCount(ProductCategory::query()->where('is_active', true), 'seo_title', 'label'),
                'warning',
                __('Two or more visible categories resolve to the exact same search title.'),
            ),
            __('Indexed out-of-stock products') => $this->metric(
                Product::query()->where('availability', 'out_of_stock')->where('robots', 'index,follow')->count(),
                'warning',
                __('Search engines can send customers to a page they cannot currently buy from. Switch robots to "No index" or update availability.'),
            ),
            __('Products missing a photo') => $this->metric(
                Product::query()->where('is_active', true)->whereNull('image_url')->count(),
                'info',
                __('These fall back to a plain coloured placeholder in search results, social shares, and the shop.'),
            ),
        ];
    }

    /**
     * @return array{value: int, severity: string, hint: string}
     */
    private function metric(int $value, string $severity, string $hint): array
    {
        return [
            'value' => $value,
            'severity' => $value > 0 ? $severity : 'ok',
            'hint' => $hint,
        ];
    }

    private function productsMissingFallbackContent(): int
    {
        return Product::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('seo_description')->orWhere('seo_description', ''))
            ->where(fn ($query) => $query->whereNull('subtitle')->orWhere('subtitle', ''))
            ->count();
    }

    /**
     * Counts records that belong to a group of two or more sharing the exact
     * same resolved title (custom SEO title, falling back to the natural title).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $query
     */
    private function duplicateTitleCount($query, string $seoTitleColumn, string $titleColumn): int
    {
        return $query
            ->selectRaw("COALESCE(NULLIF({$seoTitleColumn}, ''), {$titleColumn}) as resolved_title, COUNT(*) as total")
            ->groupBy('resolved_title')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->sum('total');
    }
}
