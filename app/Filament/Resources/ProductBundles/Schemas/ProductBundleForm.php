<?php

namespace App\Filament\Resources\ProductBundles\Schemas;

use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductVariant;
use App\Support\Admin\FilamentLocalization;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductBundleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                ToggleButtons::make('type')
                    ->options(FilamentLocalization::options(ProductBundle::TYPE_OPTIONS))
                    ->inline()
                    ->live()
                    ->required()
                    ->default('fixed_price')
                    ->helperText(__('A fixed-price bundle sells a set of products together for one combined price. Frequently-bought-together links related products with an optional per-item discount, each keeping its own price.'))
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug($state ?? ''));
                        }
                    })
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                TextInput::make('slug')
                    ->required()
                    ->helperText(__('Stable ID for this bundle.'))
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                FileUpload::make('image_url')
                    ->label('Bundle image')
                    ->disk('public')
                    ->directory('mobile/product-bundles')
                    ->visibility('public')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->imagePreviewHeight('160')
                    ->openable()
                    ->downloadable()
                    ->maxSize(4096)
                    ->columnSpan(2),
                TextInput::make('bundle_price_value')
                    ->label('Bundle price')
                    ->integer()
                    ->minValue(0)
                    ->suffix(__('Toman'))
                    ->helperText(__('The single price charged for the whole set.'))
                    ->visible(fn (Get $get): bool => $get('type') === 'fixed_price')
                    ->required(fn (Get $get): bool => $get('type') === 'fixed_price'),
                DateTimePicker::make('starts_at')
                    ->seconds(false)
                    ->helperText(__('Leave blank to start immediately.')),
                DateTimePicker::make('ends_at')
                    ->seconds(false)
                    ->helperText(__('Leave blank to run with no end date.')),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                TextInput::make('sort_order')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->default(0),
                Repeater::make('items')
                    ->label('Products in this bundle')
                    ->relationship('items', fn ($query) => $query->orderBy('sort_order'))
                    ->defaultItems(2)
                    ->columns(4)
                    ->itemLabel(fn (array $state): string => self::itemLabel($state))
                    ->addActionLabel(__('Add product'))
                    ->orderColumn('sort_order')
                    ->reorderableWithButtons()
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(fn (): array => Product::query()->orderBy('title')->pluck('title', 'id')->all())
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn (Set $set) => $set('product_variant_id', null))
                            ->columnSpan(2),
                        Select::make('product_variant_id')
                            ->label('Variant')
                            ->options(fn (Get $get): array => filled($get('product_id'))
                                ? ProductVariant::query()->where('product_id', $get('product_id'))->orderBy('name')->pluck('name', 'id')->all()
                                : [])
                            ->native(false)
                            ->searchable()
                            ->helperText(__('Optional — leave blank to use any variant.')),
                        TextInput::make('quantity')
                            ->integer()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        TextInput::make('discount_percent')
                            ->suffix('%')
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->helperText(__('Only used for "frequently bought together" bundles; ignored for fixed-price bundles.')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private static function itemLabel(array $state): string
    {
        $productId = $state['product_id'] ?? null;

        if (blank($productId)) {
            return __('New item');
        }

        return Product::query()->find($productId)?->title ?? __('New item');
    }
}
