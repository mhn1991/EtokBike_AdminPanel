<?php

namespace App\Filament\Resources\DeliveryZones;

use App\Filament\Resources\DeliveryZones\Pages\CreateDeliveryZone;
use App\Filament\Resources\DeliveryZones\Pages\EditDeliveryZone;
use App\Filament\Resources\DeliveryZones\Pages\ListDeliveryZones;
use App\Models\DeliveryZone;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryZoneResource extends Resource
{
    protected static ?string $model = DeliveryZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Delivery zones';

    protected static string|\UnitEnum|null $navigationGroup = 'Shipping';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Delivery zone')
                    ->description('Delivery fee and timing rules for a city, region, or service area.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('estimated_days')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(1),
                        TextInput::make('fee')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('minimum_order_total')
                            ->label('Minimum order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Textarea::make('notes')
                            ->rows(4)
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
                TextColumn::make('name')
                    ->description(fn (DeliveryZone $record): string => $record->code)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fee')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('minimum_order_total')
                    ->label('Minimum')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('estimated_days')
                    ->label('Days')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('lg')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTruck)
            ->emptyStateHeading('No delivery zones yet')
            ->emptyStateDescription('Create delivery zones for shipping fees and estimated delivery timing.')
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryZones::route('/'),
            'create' => CreateDeliveryZone::route('/create'),
            'edit' => EditDeliveryZone::route('/{record}/edit'),
        ];
    }
}
