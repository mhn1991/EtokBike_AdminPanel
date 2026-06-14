<?php

namespace App\Filament\Resources\PurchaseOrders;

use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Filament\Resources\PurchaseOrders\RelationManagers\ItemsRelationManager;
use App\Models\PurchaseOrder;
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

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Purchase orders';

    protected static string|\UnitEnum|null $navigationGroup = 'Purchasing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'purchase_order_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchase order')
                    ->description('Supplier order header, dates, and receiving status.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('purchase_order_number')
                            ->label('PO number')
                            ->helperText('Leave blank to auto-generate.')
                            ->maxLength(255),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        ToggleButtons::make('status')
                            ->options(PurchaseOrder::STATUS_OPTIONS)
                            ->colors([
                                'draft' => 'gray',
                                'ordered' => 'info',
                                'partially_received' => 'warning',
                                'received' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('draft'),
                        DateTimePicker::make('expected_at')
                            ->seconds(false),
                        DateTimePicker::make('received_at')
                            ->seconds(false),
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(8)
                            ->default('IRR'),
                    ]),
                Section::make('Totals')
                    ->description('Subtotal is calculated from purchase items.')
                    ->columns(4)
                    ->schema([
                        TextInput::make('subtotal')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->integer()
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('tax_total')
                            ->label('Tax')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('shipping_total')
                            ->label('Shipping')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('total')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->integer()
                            ->suffix('IRR')
                            ->default(0),
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
                TextColumn::make('purchase_order_number')
                    ->label('PO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => PurchaseOrder::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'ordered' => 'info',
                        'partially_received' => 'warning',
                        'received' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                TextColumn::make('expected_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('received_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->visibleFrom('xl')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck)
            ->emptyStateHeading('No purchase orders yet')
            ->emptyStateDescription('Create supplier orders before receiving stock.')
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(PurchaseOrder::STATUS_OPTIONS),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'edit' => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
