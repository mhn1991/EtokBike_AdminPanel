<?php

namespace App\Filament\Resources\Shipments;

use App\Filament\Resources\Shipments\Pages\CreateShipment;
use App\Filament\Resources\Shipments\Pages\EditShipment;
use App\Filament\Resources\Shipments\Pages\ListShipments;
use App\Models\Shipment;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'ارسال‌ها';

    protected static string|\UnitEnum|null $navigationGroup = 'ارسال';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ارسال';

    protected static ?string $pluralModelLabel = 'ارسال‌ها';

    protected static ?string $recordTitleAttribute = 'tracking_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Shipment'))
                    ->description(__('Track packing, carrier handoff, delivery, and failures.'))
                    ->columns(3)
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('delivery_zone_id')
                            ->label('Delivery zone')
                            ->relationship('deliveryZone', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        ToggleButtons::make('status')
                            ->options(\App\Support\Admin\FilamentLocalization::options(Shipment::STATUS_OPTIONS))
                            ->colors([
                                'pending' => 'warning',
                                'packed' => 'info',
                                'shipped' => 'primary',
                                'out_for_delivery' => 'primary',
                                'delivered' => 'success',
                                'failed' => 'danger',
                                'returned' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('pending'),
                        TextInput::make('carrier_name')
                            ->maxLength(255),
                        TextInput::make('tracking_number')
                            ->maxLength(255),
                        TextInput::make('shipping_cost')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        DateTimePicker::make('shipped_at')
                            ->seconds(false),
                        DateTimePicker::make('delivered_at')
                            ->seconds(false),
                    ]),
                Section::make(__('Delivery details'))
                    ->schema([
                        Textarea::make('delivery_address')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->placeholder(__('-'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Shipment::STATUS_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'failed', 'returned' => 'danger',
                        'packed' => 'info',
                        'shipped', 'out_for_delivery' => 'primary',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('carrier_name')
                    ->placeholder(__('-'))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('tracking_number')
                    ->placeholder(__('-'))
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('deliveryZone.name')
                    ->label('Zone')
                    ->placeholder(__('-'))
                    ->visibleFrom('lg'),
                TextColumn::make('shipped_at')
                    ->dateTime()
                    ->placeholder(__('-'))
                    ->visibleFrom('xl')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTruck)
            ->emptyStateHeading(__('No shipments yet'))
            ->emptyStateDescription(__('Create shipments for delivery orders and courier tracking.'))
            ->filters([
                SelectFilter::make('status')
                    ->options(\App\Support\Admin\FilamentLocalization::options(Shipment::STATUS_OPTIONS)),
                SelectFilter::make('delivery_zone_id')
                    ->label('Delivery zone')
                    ->relationship('deliveryZone', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShipments::route('/'),
            'create' => CreateShipment::route('/create'),
            'edit' => EditShipment::route('/{record}/edit'),
        ];
    }
}
