<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Admin\FilamentLocalization;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->description(__('Core shop listing details shown in the mobile app.'))
                    ->columns(3)
                    ->schema([
                        Select::make('product_category_id')
                            ->label('Category')
                            ->options(fn (): array => ProductCategory::formOptions())
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
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
                            ->columnSpanFull(),
                    ]),
                Section::make('Publishing controls')
                    ->description(__('Visibility and stable app identifiers. Use table drag ordering for day-to-day sorting.'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->helperText(__('Stable product ID used by the mobile app.'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->helperText(__('Optional warehouse SKU or barcode used for stock matching.'))
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_featured')
                            ->label('Featured in app')
                            ->required()
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('Pricing and stock')
                    ->description(__('Price values are numeric; the label can override the app-facing text.'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_value')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('price_label')
                            ->maxLength(255)
                            ->helperText(__('Shown in the app. Leave blank to format from price value.')),
                        TextInput::make('stock_label')
                            ->maxLength(255),
                    ]),
                Section::make('Variants')
                    ->description(__('Add color, size, extra features, stock amount, and variant price for versions of the same product.'))
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
                                    ->helperText(__('Shown to staff and customers for this exact option.'))
                                    ->live(onBlur: true)
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                TextInput::make('sku')
                                    ->label('Variant SKU')
                                    ->maxLength(255)
                                    ->unique(table: 'product_variants', column: 'sku', ignoreRecord: true)
                                    ->columnSpan(2),
                                TextInput::make('options.color')
                                    ->label('Color')
                                    ->placeholder(__('Red, Black, Blue...'))
                                    ->maxLength(80),
                                TextInput::make('options.size')
                                    ->label('Size')
                                    ->placeholder(__('S, M, L, 26, 29...'))
                                    ->maxLength(80),
                                KeyValue::make('options.attributes')
                                    ->label('Extra features')
                                    ->keyLabel('Feature')
                                    ->valueLabel('Value')
                                    ->helperText(__('Add any filterable feature, such as brand, material, model, or gender.'))
                                    ->columnSpanFull(),
                                TextInput::make('stock_quantity')
                                    ->label('Amount')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0),
                                TextInput::make('price_value')
                                    ->label('Variant price')
                                    ->integer()
                                    ->minValue(0)
                                    ->suffix('IRR')
                                    ->helperText(__('Leave empty to use the base product price.')),
                                TextInput::make('minimum_stock')
                                    ->label('Low stock alert')
                                    ->required()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->required()
                                    ->default(true),
                                FileUpload::make('image_url')
                                    ->label('Variant image')
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
                Section::make('Warehouse')
                    ->description(__('Use stock movements to change quantity after a product is created.'))
                    ->columns(4)
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label('On hand')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextInput::make('reserved_quantity')
                            ->label('Reserved')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('minimum_stock')
                            ->label('Low stock alert')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('warehouse_location')
                            ->label('Location')
                            ->placeholder(__('Main warehouse / Aisle A1'))
                            ->maxLength(255),
                    ]),
                Section::make('App card')
                    ->description(__('Thumbnail and description used in product lists and detail views.'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('thumbnail_text')
                            ->required()
                            ->maxLength(255)
                            ->default('ETOK'),
                        ColorPicker::make('thumbnail_color')
                            ->required()
                            ->hex()
                            ->default('#101114'),
                        FileUpload::make('image_url')
                            ->label('Product image')
                            ->helperText(__('Use the edit control to adjust the crop, upload a new file to replace it, or remove it and save to return to the fallback thumbnail.'))
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
                            ->imagePreviewHeight('160')
                            ->panelLayout('integrated')
                            ->removeUploadedFileButtonPosition('left bottom')
                            ->uploadButtonPosition('right bottom')
                            ->uploadProgressIndicatorPosition('right bottom')
                            ->deletable()
                            ->openable()
                            ->downloadable()
                            ->maxSize(4096),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('SEO')
                    ->description(__('Controls the public product page metadata, social preview, and sitemap settings.'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Meta title')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('seo_description')
                            ->label('Meta description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('canonical_url')
                            ->maxLength(255),
                        Select::make('robots')
                            ->options(FilamentLocalization::options(Product::ROBOTS_OPTIONS))
                            ->native(false)
                            ->required()
                            ->default('index,follow'),
                        Toggle::make('include_in_sitemap')
                            ->label('Include in sitemap')
                            ->default(true),
                        TextInput::make('og_title')
                            ->label('Social title')
                            ->maxLength(255),
                        Textarea::make('og_description')
                            ->label('Social description')
                            ->rows(3)
                            ->maxLength(500),
                        FileUpload::make('og_image')
                            ->label('Social image')
                            ->disk('public')
                            ->directory('seo/products')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('140')
                            ->openable()
                            ->downloadable(),
                        TextInput::make('sitemap_priority')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->default(0.7),
                        Select::make('sitemap_change_frequency')
                            ->options(FilamentLocalization::options(Product::CHANGE_FREQUENCY_OPTIONS))
                            ->native(false)
                            ->required()
                            ->default('weekly'),
                    ]),
            ]);
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
