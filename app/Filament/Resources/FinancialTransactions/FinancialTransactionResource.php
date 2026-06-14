<?php

namespace App\Filament\Resources\FinancialTransactions;

use App\Filament\Resources\FinancialTransactions\Pages\CreateFinancialTransaction;
use App\Filament\Resources\FinancialTransactions\Pages\EditFinancialTransaction;
use App\Filament\Resources\FinancialTransactions\Pages\ListFinancialTransactions;
use App\Models\FinancialTransaction;
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

class FinancialTransactionResource extends Resource
{
    protected static ?string $model = FinancialTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static ?string $navigationLabel = 'Finance';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'financial transaction';

    protected static ?string $pluralModelLabel = 'financial transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction')
                    ->description('Track money moving in or out of the ecommerce operation.')
                    ->columns(3)
                    ->schema([
                        ToggleButtons::make('direction')
                            ->options(FinancialTransaction::DIRECTION_OPTIONS)
                            ->colors([
                                'income' => 'success',
                                'expense' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('income'),
                        Select::make('type')
                            ->options(FinancialTransaction::TYPE_OPTIONS)
                            ->native(false)
                            ->required()
                            ->default('sale_income'),
                        ToggleButtons::make('status')
                            ->options(FinancialTransaction::STATUS_OPTIONS)
                            ->colors([
                                'pending' => 'warning',
                                'posted' => 'success',
                                'void' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('pending'),
                        TextInput::make('amount')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(8)
                            ->default('IRR'),
                        DateTimePicker::make('occurred_at')
                            ->seconds(false)
                            ->default(now()),
                        TextInput::make('payment_method')
                            ->placeholder('Cash, card, bank transfer')
                            ->maxLength(255),
                        TextInput::make('reference')
                            ->placeholder('Receipt, bank ref, invoice')
                            ->maxLength(255),
                    ]),
                Section::make('Links')
                    ->columns(3)
                    ->schema([
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('purchase_order_id')
                            ->label('Purchase order')
                            ->relationship('purchaseOrder', 'purchase_order_number')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
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
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FinancialTransaction::TYPE_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'sale_income' => 'success',
                        'refund', 'supplier_payment', 'expense' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->color(fn (FinancialTransaction $record): string => $record->direction === 'income' ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => FinancialTransaction::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'posted' => 'success',
                        'void' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->placeholder('-')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->placeholder('-')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('reference')
                    ->placeholder('-')
                    ->searchable()
                    ->visibleFrom('xl'),
            ])
            ->emptyStateIcon(Heroicon::CurrencyDollar)
            ->emptyStateHeading('No finance records yet')
            ->emptyStateDescription('Record sales income, refunds, supplier payments, and expenses.')
            ->filters([
                SelectFilter::make('type')
                    ->options(FinancialTransaction::TYPE_OPTIONS),
                SelectFilter::make('direction')
                    ->options(FinancialTransaction::DIRECTION_OPTIONS),
                SelectFilter::make('status')
                    ->options(FinancialTransaction::STATUS_OPTIONS),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialTransactions::route('/'),
            'create' => CreateFinancialTransaction::route('/create'),
            'edit' => EditFinancialTransaction::route('/{record}/edit'),
        ];
    }
}
