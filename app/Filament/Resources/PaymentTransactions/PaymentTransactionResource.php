<?php

namespace App\Filament\Resources\PaymentTransactions;

use App\Filament\Resources\PaymentTransactions\Pages\CreatePaymentTransaction;
use App\Filament\Resources\PaymentTransactions\Pages\EditPaymentTransaction;
use App\Filament\Resources\PaymentTransactions\Pages\ListPaymentTransactions;
use App\Models\PaymentTransaction;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
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

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Payments';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment')
                ->description('Track manual or gateway payment attempts and reconciliation references.')
                ->columns(3)
                ->schema([
                    Select::make('order_id')->label('Order')->relationship('order', 'order_number')->native(false)->searchable()->preload(),
                    Select::make('financial_transaction_id')->label('Finance record')->relationship('financialTransaction', 'reference')->native(false)->searchable()->preload(),
                    ToggleButtons::make('status')->options(PaymentTransaction::STATUS_OPTIONS)->colors([
                        'pending' => 'warning',
                        'authorized' => 'info',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'danger',
                        'cancelled' => 'gray',
                    ])->inline()->required()->default('pending'),
                    TextInput::make('provider')->required()->maxLength(255)->default('manual'),
                    TextInput::make('amount')->required()->integer()->minValue(0)->suffix('IRR')->default(0),
                    TextInput::make('currency')->required()->maxLength(8)->default('IRR'),
                    TextInput::make('transaction_reference')->maxLength(255),
                    TextInput::make('authorization_code')->maxLength(255),
                    DateTimePicker::make('attempted_at')->seconds(false),
                    DateTimePicker::make('paid_at')->seconds(false),
                    Textarea::make('failure_reason')->rows(3)->columnSpanFull(),
                    KeyValue::make('gateway_payload')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false)->striped()->columns([
            TextColumn::make('order.order_number')->label('Order')->placeholder('-')->searchable()->sortable(),
            TextColumn::make('provider')->searchable()->sortable(),
            TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => PaymentTransaction::STATUS_OPTIONS[$state] ?? $state)->color(fn (string $state): string => match ($state) {
                'paid' => 'success',
                'authorized' => 'info',
                'failed', 'refunded' => 'danger',
                'cancelled' => 'gray',
                default => 'warning',
            })->sortable(),
            TextColumn::make('amount')->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))->sortable(),
            TextColumn::make('transaction_reference')->placeholder('-')->visibleFrom('lg')->searchable(),
            TextColumn::make('paid_at')->dateTime()->placeholder('-')->visibleFrom('xl')->sortable(),
        ])->emptyStateIcon(Heroicon::OutlinedCreditCard)
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Track gateway attempts, manual payments, failed payments, and reconciliation references.')
            ->filters([SelectFilter::make('status')->options(PaymentTransaction::STATUS_OPTIONS)])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentTransactions::route('/'),
            'create' => CreatePaymentTransaction::route('/create'),
            'edit' => EditPaymentTransaction::route('/{record}/edit'),
        ];
    }
}
