<?php

namespace App\Filament\Resources\ProductUnits;

use App\Filament\Resources\ProductUnits\Pages\CreateProductUnit;
use App\Filament\Resources\ProductUnits\Pages\EditProductUnit;
use App\Filament\Resources\ProductUnits\Pages\ListProductUnits;
use App\Models\ProductUnit;
use BackedEnum;
use Filament\Actions\EditAction;
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

class ProductUnitResource extends Resource
{
    protected static ?string $model = ProductUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Product units';

    protected static string|\UnitEnum|null $navigationGroup = 'Warehouse';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'product unit';

    protected static ?string $pluralModelLabel = 'product units';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Unit conversion')
                    ->description('Define packaging as a quantity of the product base unit.')
                    ->columns(3)
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->placeholder('Piece, box, pallet')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('abbreviation')
                            ->placeholder('pc, box, plt')
                            ->maxLength(32),
                        TextInput::make('quantity_in_base_units')
                            ->label('Base-unit quantity')
                            ->helperText('Example: box = 20 pieces, pallet = 2000 pieces.')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        Toggle::make('is_base_unit')
                            ->label('Base unit')
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('product.title')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->description(fn (ProductUnit $record): string => $record->abbreviation ?: '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_in_base_units')
                    ->label('Base units')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                IconColumn::make('is_base_unit')
                    ->label('Base')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('lg')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading('No product units yet')
            ->emptyStateDescription('Create piece, box, pallet, or other product-specific units.')
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductUnits::route('/'),
            'create' => CreateProductUnit::route('/create'),
            'edit' => EditProductUnit::route('/{record}/edit'),
        ];
    }
}
