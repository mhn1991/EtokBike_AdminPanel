<?php

namespace App\Filament\Pages;

use App\Models\ContentPage;
use App\Models\Product;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SeoAudit extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'SEO audit';

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'SEO audit';

    protected string $view = 'filament.pages.seo-audit';

    /**
     * @return array<string, int>
     */
    public function metrics(): array
    {
        return [
            'Products missing meta title' => Product::query()->where('is_active', true)->whereNull('seo_title')->count(),
            'Products missing meta description' => Product::query()->where('is_active', true)->whereNull('seo_description')->count(),
            'Products missing image' => Product::query()->where('is_active', true)->whereNull('image_url')->count(),
            'Indexed out-of-stock products' => Product::query()->where('availability', 'out_of_stock')->where('robots', 'index,follow')->count(),
            'Categories missing meta description' => ProductCategory::query()->where('is_active', true)->whereNull('seo_description')->count(),
            'Content pages missing meta description' => ContentPage::query()->where('is_active', true)->whereNull('seo_description')->count(),
        ];
    }
}
