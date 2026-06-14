<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->description('Organizes product lists in the mobile shop.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('label')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('SEO')
                    ->description('Controls category page metadata, social preview, and sitemap settings.')
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
                            ->options(ProductCategory::ROBOTS_OPTIONS)
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
                            ->directory('seo/categories')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('140')
                            ->openable()
                            ->downloadable(),
                        TextInput::make('sitemap_priority')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(1)
                            ->default(0.8),
                        Select::make('sitemap_change_frequency')
                            ->options(ProductCategory::CHANGE_FREQUENCY_OPTIONS)
                            ->native(false)
                            ->required()
                            ->default('weekly'),
                    ]),
            ]);
    }
}
