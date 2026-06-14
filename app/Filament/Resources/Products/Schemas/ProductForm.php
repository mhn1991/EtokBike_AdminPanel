<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
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
                    ->description('Core shop listing details shown in the mobile app.')
                    ->columns(3)
                    ->schema([
                        Select::make('product_category_id')
                            ->label('Category')
                            ->relationship('category', 'label')
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
                            ->options(Product::AVAILABILITY_OPTIONS)
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
                    ->description('Visibility and stable app identifiers. Use table drag ordering for day-to-day sorting.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('slug')
                            ->required()
                            ->helperText('Stable product ID used by the mobile app.')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('sku')
                            ->label('SKU')
                            ->helperText('Optional warehouse SKU or barcode used for stock matching.')
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
                    ->description('Price values are numeric; the label can override the app-facing text.')
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
                            ->helperText('Shown in the app. Leave blank to format from price value.'),
                        TextInput::make('stock_label')
                            ->maxLength(255),
                    ]),
                Section::make('Warehouse')
                    ->description('Use stock movements to change quantity after a product is created.')
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
                            ->placeholder('Main warehouse / Aisle A1')
                            ->maxLength(255),
                    ]),
                Section::make('App card')
                    ->description('Thumbnail and description used in product lists and detail views.')
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
                            ->disk('public')
                            ->directory('mobile/products')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('160')
                            ->openable()
                            ->downloadable()
                            ->maxSize(4096),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('SEO')
                    ->description('Controls the public product page metadata, social preview, and sitemap settings.')
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
                            ->options(Product::ROBOTS_OPTIONS)
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
                            ->options(Product::CHANGE_FREQUENCY_OPTIONS)
                            ->native(false)
                            ->required()
                            ->default('weekly'),
                    ]),
            ]);
    }
}
