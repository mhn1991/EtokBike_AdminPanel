<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->columns(3)
                    ->schema([
                        Select::make('product_category_id')
                            ->label('Category')
                            ->relationship('category', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Select::make('availability')
                            ->options(Product::AVAILABILITY_OPTIONS)
                            ->required()
                            ->default('in_stock'),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_featured')
                            ->required()
                            ->default(false),
                        Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('Pricing and stock')
                    ->columns(3)
                    ->schema([
                        TextInput::make('price_value')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('price_label')
                            ->maxLength(255)
                            ->helperText('Shown in the app. Leave blank to format from price value.'),
                        TextInput::make('stock_label')
                            ->maxLength(255),
                    ]),
                Section::make('App card')
                    ->columns(3)
                    ->schema([
                        TextInput::make('thumbnail_text')
                            ->required()
                            ->maxLength(255)
                            ->default('ETOK'),
                        TextInput::make('thumbnail_color')
                            ->required()
                            ->maxLength(16)
                            ->default('#101114'),
                        TextInput::make('image_url')
                            ->url()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
