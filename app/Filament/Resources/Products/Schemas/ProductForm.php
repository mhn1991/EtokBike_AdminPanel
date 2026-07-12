<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Admin\FilamentLocalization;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('slug_is_custom')
                    ->default(fn (?Product $record): bool => (bool) $record?->exists)
                    ->dehydrated(false),
                Grid::make([
                    'default' => 1,
                    'xl' => 3,
                ])->schema([
                    Group::make([
                        Section::make(__('Product content'))
                            ->description(__('Write the customer-facing product information here. Start with the name, a short summary, and then the complete description.'))
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?Product $record, ?string $state): void {
                                        if ($record?->exists || $get('slug_is_custom')) {
                                            return;
                                        }

                                        $set('slug', self::suggestUniqueSlug($state, $record));
                                    })
                                    ->helperText(__('The main product name customers see in the shop, app, browser title, and order lines. Include the brand and model when useful.'))
                                    ->placeholder(__('Example: Trek Marlin 7 mountain bike'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('subtitle')
                                    ->required()
                                    ->helperText(__('A short one-sentence benefit shown under the title. Keep it specific and avoid repeating the product name.'))
                                    ->placeholder(__('Example: Lightweight aluminium frame with hydraulic disc brakes'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Select::make('product_category_id')
                                    ->label('Category')
                                    ->options(fn (): array => ProductCategory::formOptions())
                                    ->native(false)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText(__('Choose the most specific category. It controls where the product appears and which shop filters customers can use.'))
                                    ->columnSpanFull(),
                                RichEditor::make('description')
                                    ->label('Full description')
                                    ->helperText(__('Use headings, short paragraphs, lists, links, tables, and images to explain features, fit, included items, and who the product suits. Price and stock are managed below.'))
                                    ->placeholder(__('Write the complete product description...'))
                                    ->toolbarButtons([
                                        ['bold', 'italic', 'underline', 'link'],
                                        ['h2', 'h3'],
                                        ['alignStart', 'alignCenter', 'alignEnd'],
                                        ['blockquote', 'bulletList', 'orderedList'],
                                        ['table', 'attachFiles'],
                                        ['undo', 'redo'],
                                    ])
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('mobile/product-descriptions')
                                    ->fileAttachmentsVisibility('public')
                                    ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->fileAttachmentsMaxSize(4096)
                                    ->preventFileAttachmentPathTampering()
                                    ->resizableImages()
                                    ->extraInputAttributes(['style' => 'min-height: 20rem;'])
                                    ->columnSpanFull(),
                            ]),
                        Section::make(__('Product media'))
                            ->description(__('Upload the main product photo used on shop cards and the product page. The fallback fields are only used when no photo is available.'))
                            ->icon(Heroicon::OutlinedPhoto)
                            ->columns(2)
                            ->schema([
                                FileUpload::make('image_url')
                                    ->label('Product image')
                                    ->helperText(__('Upload a clear JPG, PNG, or WebP image up to 4 MB. A 4:3 crop works best across the website and app; use the edit control to crop it.'))
                                    ->disk('public')
                                    ->directory('mobile/products')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->imageEditor()
                                    ->imageEditorAspectRatioOptions([
                                        '4:3',
                                        '1:1',
                                        '16:9',
                                    ])
                                    ->imagePreviewHeight('220')
                                    ->panelLayout('integrated')
                                    ->removeUploadedFileButtonPosition('left bottom')
                                    ->uploadButtonPosition('right bottom')
                                    ->uploadProgressIndicatorPosition('right bottom')
                                    ->deletable()
                                    ->openable()
                                    ->downloadable()
                                    ->maxSize(4096)
                                    ->columnSpanFull(),
                                TextInput::make('thumbnail_text')
                                    ->required()
                                    ->helperText(__('Short fallback text shown inside the coloured placeholder when the product has no image. Use a model name or 2–8 characters.'))
                                    ->placeholder(__('Example: MARLIN'))
                                    ->maxLength(255)
                                    ->default('ETOK'),
                                ColorPicker::make('thumbnail_color')
                                    ->required()
                                    ->hex()
                                    ->helperText(__('Background colour for the fallback thumbnail. It is hidden whenever a product image exists.'))
                                    ->default('#101114'),
                            ]),
                        Section::make(__('Pricing and availability'))
                            ->description(__('Set the amount used for calculations and the customer-facing availability message.'))
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->columns(2)
                            ->schema([
                                TextInput::make('price_value')
                                    ->label('Base price')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->suffix(__('Toman'))
                                    ->helperText(__('The base price in toman used for the basket, checkout, orders, and reports. Enter numbers only.'))
                                    ->default(0),
                                TextInput::make('price_label')
                                    ->label('Displayed price text')
                                    ->maxLength(255)
                                    ->placeholder(__('Example: From 28,500,000 toman'))
                                    ->helperText(__('Optional display-only text for the shop and app. Leave blank to format the base price automatically; this does not change order calculations.')),
                                ToggleButtons::make('availability')
                                    ->options(FilamentLocalization::options(Product::AVAILABILITY_OPTIONS))
                                    ->colors([
                                        'in_stock' => 'success',
                                        'low_stock' => 'warning',
                                        'orderable' => 'info',
                                        'out_of_stock' => 'danger',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->default('in_stock')
                                    ->helperText(__('Controls the availability badge and whether customers can order. Use “Orderable” for items you can source even when physical stock is zero.'))
                                    ->columnSpanFull(),
                                TextInput::make('stock_label')
                                    ->label('Custom stock message')
                                    ->maxLength(255)
                                    ->placeholder(__('Example: Only 2 left or delivered in 5 days'))
                                    ->helperText(__('Optional message shown instead of the standard availability label. It does not change the actual warehouse quantity.'))
                                    ->columnSpanFull(),
                            ]),
                        Section::make(__('Variants'))
                            ->description(__('Use variants only when customers choose between versions such as colour, size, or frame type. Each variant can have its own SKU, price, image, and stock.'))
                            ->icon(Heroicon::OutlinedSwatch)
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->schema([
                                Repeater::make('variants')
                                    ->label('Product variants')
                                    ->relationship('variants', fn ($query) => $query->orderBy('sort_order')->orderBy('name'))
                                    ->defaultItems(0)
                                    ->columns(4)
                                    ->itemLabel(fn (array $state): string => self::variantItemLabel($state))
                                    ->addActionLabel(__('Add variant'))
                                    ->orderColumn('sort_order')
                                    ->reorderableWithButtons()
                                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::normalizeVariantData($data))
                                    ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::normalizeVariantData($data))
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Variant name')
                                            ->placeholder(__('Red / Large'))
                                            ->helperText(__('The exact option name shown to staff and customers, usually a combination such as colour and size.'))
                                            ->live(onBlur: true)
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),
                                        TextInput::make('sku')
                                            ->label('Variant SKU')
                                            ->helperText(__('A unique warehouse or barcode code for this exact variant. Do not reuse the base product SKU.'))
                                            ->maxLength(255)
                                            ->unique(table: 'product_variants', column: 'sku', ignoreRecord: true)
                                            ->columnSpan(2),
                                        TextInput::make('options.color')
                                            ->label('Color')
                                            ->placeholder(__('Red, Black, Blue...'))
                                            ->helperText(__('The customer-facing colour value used in choices and filters. Leave blank if colour does not vary.'))
                                            ->maxLength(80),
                                        TextInput::make('options.size')
                                            ->label('Size')
                                            ->placeholder(__('S, M, L, 26, 29...'))
                                            ->helperText(__('The customer-facing size or wheel value used in choices and filters. Leave blank if size does not vary.'))
                                            ->maxLength(80),
                                        KeyValue::make('options.attributes')
                                            ->label('Extra features')
                                            ->keyLabel('Feature')
                                            ->valueLabel('Value')
                                            ->helperText(__('Add other characteristics that differ for this variant, such as frame material, brake type, model year, or gender.'))
                                            ->columnSpanFull(),
                                        TextInput::make('stock_quantity')
                                            ->label('Variant stock')
                                            ->required()
                                            ->integer()
                                            ->minValue(0)
                                            ->helperText(__('Sellable units currently on hand for this exact variant.'))
                                            ->default(0),
                                        TextInput::make('price_value')
                                            ->label('Variant price')
                                            ->integer()
                                            ->minValue(0)
                                            ->suffix(__('Toman'))
                                            ->helperText(__('Leave blank to use the base product price; enter a value only when this variant costs a different amount.')),
                                        TextInput::make('minimum_stock')
                                            ->label('Low stock alert')
                                            ->required()
                                            ->integer()
                                            ->minValue(0)
                                            ->helperText(__('The warning threshold for this variant. A value of 0 disables the low-stock warning.'))
                                            ->default(0),
                                        Toggle::make('is_active')
                                            ->label('Active')
                                            ->required()
                                            ->helperText(__('Turn this off to hide the variant without deleting its history or SKU.'))
                                            ->default(true),
                                        FileUpload::make('image_url')
                                            ->label('Variant image')
                                            ->helperText(__('Optional image that replaces the main product photo when this variant is selected. Use the same 4:3 style as the main image.'))
                                            ->disk('public')
                                            ->directory('mobile/product-variants')
                                            ->visibility('public')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->imageEditor()
                                            ->imageEditorAspectRatioOptions([
                                                '4:3',
                                                '1:1',
                                                '16:9',
                                            ])
                                            ->imagePreviewHeight('140')
                                            ->panelLayout('integrated')
                                            ->openable()
                                            ->downloadable()
                                            ->maxSize(4096)
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Section::make(__('Warehouse'))
                            ->description(__('Inventory quantities are kept separate from the public availability message. Use stock movements for audited on-hand changes after the product is created.'))
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->columns(2)
                            ->schema([
                                TextInput::make('stock_quantity')
                                    ->label('On hand')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText(__('Read-only physical stock calculated from inventory movements. Create the product first, then record receiving or an adjustment.'))
                                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                                TextInput::make('reserved_quantity')
                                    ->label('Reserved')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->helperText(__('Units already allocated to open orders and therefore not available to sell. Normally this is maintained by the order workflow.'))
                                    ->default(0),
                                TextInput::make('minimum_stock')
                                    ->label('Low stock alert')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->helperText(__('Show a warning when available stock reaches this number. Use 0 when no alert is needed.'))
                                    ->default(0),
                                TextInput::make('warehouse_location')
                                    ->label('Location')
                                    ->placeholder(__('Main warehouse / Aisle A1'))
                                    ->helperText(__('Internal shelf, aisle, or storage location that helps staff find the product. Customers never see this value.'))
                                    ->maxLength(255),
                            ]),
                        Section::make(__('SEO and sharing'))
                            ->description(__('Optional search and social settings. Leave fields blank to use the product title, summary, description, and main image automatically.'))
                            ->icon(Heroicon::OutlinedMagnifyingGlass)
                            ->collapsible()
                            ->collapsed()
                            ->persistCollapsed()
                            ->columns(2)
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label('Meta title')
                                    ->helperText(__('Optional search-result title. Aim for about 50–60 characters; leave blank to use the product title and site name.'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('seo_description')
                                    ->label('Meta description')
                                    ->helperText(__('Optional search-result summary. Aim for about 140–160 characters; leave blank to use the product description or subtitle.'))
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->helperText(__('Advanced: enter the preferred full URL only when this content is duplicated elsewhere. Normally leave this blank.'))
                                    ->placeholder('https://example.com/shop/product')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Select::make('robots')
                                    ->options(FilamentLocalization::options(Product::ROBOTS_OPTIONS))
                                    ->native(false)
                                    ->required()
                                    ->helperText(__('Controls whether search engines may index this product and follow its links. “Index, follow” is correct for normal products.'))
                                    ->default('index,follow'),
                                Toggle::make('include_in_sitemap')
                                    ->label('Include in sitemap')
                                    ->helperText(__('Keep enabled so search engines can discover active products. Disable it for private, duplicate, or temporary pages.'))
                                    ->default(true),
                                TextInput::make('og_title')
                                    ->label('Social title')
                                    ->helperText(__('Optional title used when this product is shared on social networks. Leave blank to reuse the SEO title.'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('og_description')
                                    ->label('Social description')
                                    ->helperText(__('Optional summary used in social previews. Leave blank to reuse the search description.'))
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                FileUpload::make('og_image')
                                    ->label('Social image')
                                    ->helperText(__('Optional image used when sharing the product. Use a wide 1200×630 image; leave blank to use the main product photo.'))
                                    ->disk('public')
                                    ->directory('seo/products')
                                    ->visibility('public')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(4096)
                                    ->imagePreviewHeight('140')
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpanFull(),
                                TextInput::make('sitemap_priority')
                                    ->label('Sitemap priority')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(1)
                                    ->step(0.1)
                                    ->helperText(__('Relative importance from 0.0 to 1.0 for search crawlers. The default 0.7 suits most products.'))
                                    ->default(0.7),
                                Select::make('sitemap_change_frequency')
                                    ->label('Sitemap change frequency')
                                    ->options(FilamentLocalization::options(Product::CHANGE_FREQUENCY_OPTIONS))
                                    ->native(false)
                                    ->required()
                                    ->helperText(__('A hint for how often this page usually changes. Weekly is suitable for price and stock updates.'))
                                    ->default('weekly'),
                            ]),
                    ])->columnSpan([
                        'default' => 1,
                        'xl' => 2,
                    ]),
                    Group::make([
                        Section::make(__('Publishing'))
                            ->description(__('Save as a visible product or keep it hidden while you finish the content.'))
                            ->icon(Heroicon::OutlinedRocketLaunch)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Visible in shop and app')
                                    ->helperText(__('Turn this off to save a draft. Hidden products remain in the admin panel but customers cannot open or buy them.'))
                                    ->required()
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('Featured product')
                                    ->helperText(__('Highlights this product in featured sections. Use sparingly so featured lists stay useful.'))
                                    ->required()
                                    ->default(false),
                                TextInput::make('slug')
                                    ->label('Slug / URL')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state): void {
                                        $set('slug', self::normalizeSlug($state));
                                        $set('slug_is_custom', true);
                                    })
                                    ->dehydrateStateUsing(fn (?string $state): string => self::normalizeSlug($state))
                                    ->unique(ignoreRecord: true)
                                    ->hint(fn (Get $get): string => $get('slug_is_custom') ? __('Custom slug') : __('Suggested from the title'))
                                    ->helperText(__('The unique, readable part of the product URL and the stable ID used by the app. It is suggested from the title; edit it if needed. Avoid changing it after publishing because old links may stop working.'))
                                    ->suffixAction(
                                        Action::make('suggestSlug')
                                            ->label(__('Suggest from title'))
                                            ->icon(Heroicon::OutlinedArrowPath)
                                            ->tooltip(__('Replace the slug with a new unique suggestion from the current title'))
                                            ->disabled(fn (Get $get): bool => blank($get('title')))
                                            ->action(function (Get $get, Set $set, ?Product $record): void {
                                                $set('slug', self::suggestUniqueSlug($get('title'), $record));
                                                $set('slug_is_custom', true);
                                            }),
                                    )
                                    ->maxLength(255),
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->helperText(__('Optional unique stock-keeping unit or barcode for the base product. If variants have their own SKUs, this can identify the overall model.'))
                                    ->placeholder(__('Example: TREK-MARLIN-7'))
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('sort_order')
                                    ->label('Sort order')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->helperText(__('Lower numbers appear earlier when products have the same featured status. You can also reorder products from the product list.'))
                                    ->default(0),
                            ]),
                    ])->columnSpan([
                        'default' => 1,
                        'xl' => 1,
                    ]),
                ]),
            ]);
    }

    private static function normalizeSlug(?string $value): string
    {
        $value = str_replace(["\u{200C}", "\u{200D}"], ' ', trim((string) $value));

        return Str::slug($value, '-', null);
    }

    private static function suggestUniqueSlug(?string $title, ?Product $record = null): string
    {
        $baseSlug = self::normalizeSlug($title);
        $baseSlug = rtrim(Str::limit($baseSlug ?: 'product', 240, ''), '-');
        $candidate = $baseSlug;
        $suffix = 2;

        while (self::slugExists($candidate, $record)) {
            $candidate = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $candidate;
    }

    private static function slugExists(string $slug, ?Product $record = null): bool
    {
        return Product::query()
            ->where('slug', $slug)
            ->when($record?->exists, fn ($query) => $query->whereKeyNot($record->getKey()))
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private static function variantItemLabel(array $state): string
    {
        $label = trim((string) ($state['name'] ?? ''));

        if ($label !== '') {
            return $label;
        }

        $options = collect($state['options'] ?? [])
            ->only(['color', 'size'])
            ->filter(fn (mixed $value): bool => filled($value))
            ->join(' / ');

        return $options !== '' ? $options : __('New variant');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function normalizeVariantData(array $data): array
    {
        $options = collect($data['options'] ?? [])
            ->map(function (mixed $value): mixed {
                if (is_array($value)) {
                    $value = collect($value)
                        ->map(fn (mixed $item): mixed => is_string($item) ? trim($item) : $item)
                        ->filter(fn (mixed $item): bool => filled($item) && ! is_array($item))
                        ->all();

                    return $value === [] ? null : $value;
                }

                return is_string($value) ? trim($value) : $value;
            })
            ->filter(fn (mixed $value): bool => filled($value))
            ->all();

        $data['options'] = $options === [] ? null : $options;
        $data['price_value'] = filled($data['price_value'] ?? null) ? (int) $data['price_value'] : null;

        return $data;
    }
}
