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
            ]);
    }
}
