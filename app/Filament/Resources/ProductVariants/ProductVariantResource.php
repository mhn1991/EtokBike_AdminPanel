<?php

namespace App\Filament\Resources\ProductVariants;

use App\Filament\Resources\ProductVariants\Pages\CreateProductVariant;
use App\Filament\Resources\ProductVariants\Pages\EditProductVariant;
use App\Filament\Resources\ProductVariants\Pages\ListProductVariants;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Product variants';

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Variant')
                ->description('Variant-specific SKU, options, price, and stock.')
                ->columns(3)
                ->schema([
                    Select::make('product_id')->label('Product')->relationship('product', 'title')->native(false)->searchable()->preload()->required(),
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('sku')->label('SKU')->maxLength(255)->unique(ignoreRecord: true),
                    KeyValue::make('options')->keyLabel('Option')->valueLabel('Value')->columnSpanFull(),
                    TextInput::make('price_value')->label('Variant price')->integer()->minValue(0)->suffix('IRR'),
                    TextInput::make('stock_quantity')->required()->integer()->minValue(0)->default(0),
                    TextInput::make('minimum_stock')->required()->integer()->minValue(0)->default(0),
                    TextInput::make('sort_order')->required()->integer()->minValue(0)->default(0),
                    Toggle::make('is_active')->default(true),
                    FileUpload::make('image_url')
                        ->label('Variant image')
                        ->disk('public')
                        ->directory('mobile/product-variants')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('140')
                        ->openable()
                        ->downloadable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('product.title')->label('Product')->searchable()->sortable(),
                TextColumn::make('name')->description(fn (ProductVariant $record): string => $record->sku ?: '')->searchable()->sortable()->wrap(),
                TextColumn::make('price_value')->label('Price')->formatStateUsing(fn (?int $state): string => filled($state) ? number_format($state) : '-')->visibleFrom('md')->sortable(),
                TextColumn::make('stock_quantity')->label('Stock')->badge()->color(fn (ProductVariant $record): string => $record->stock_quantity <= $record->minimum_stock ? 'warning' : 'success')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedSquares2x2)
            ->emptyStateHeading('No variants yet')
            ->emptyStateDescription('Create size, color, model, or bundle variants for products.')
            ->filters([
                SelectFilter::make('product_id')->label('Product')->relationship('product', 'title')->searchable()->preload(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductVariants::route('/'),
            'create' => CreateProductVariant::route('/create'),
            'edit' => EditProductVariant::route('/{record}/edit'),
        ];
    }
}
