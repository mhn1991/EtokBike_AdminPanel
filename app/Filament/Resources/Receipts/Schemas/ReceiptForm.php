<?php

namespace App\Filament\Resources\Receipts\Schemas;

use App\Models\Receipt;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt')
                    ->description('Receipt identity, linked records, and issue status.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('receipt_number')
                            ->helperText('Leave blank to auto-generate.')
                            ->maxLength(255),
                        Select::make('type')
                            ->options(Receipt::TYPE_OPTIONS)
                            ->native(false)
                            ->required()
                            ->default('receipt'),
                        ToggleButtons::make('status')
                            ->options(Receipt::STATUS_OPTIONS)
                            ->colors([
                                'draft' => 'warning',
                                'issued' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('issued'),
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        Select::make('return_request_id')
                            ->label('Return')
                            ->relationship('returnRequest', 'return_number')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('issued_at')
                            ->seconds(false)
                            ->default(now()),
                        DateTimePicker::make('cancelled_at')
                            ->seconds(false),
                    ]),
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('customer_phone')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('billing_address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Payment and totals')
                    ->columns(4)
                    ->schema([
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(8)
                            ->default('IRR'),
                        TextInput::make('payment_method')
                            ->maxLength(255),
                        TextInput::make('payment_status')
                            ->maxLength(255),
                        TextInput::make('pdf_path')
                            ->label('PDF path')
                            ->maxLength(255),
                        TextInput::make('subtotal')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('discount_total')
                            ->label('Discount')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('delivery_total')
                            ->label('Delivery')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('tax_total')
                            ->label('Tax')
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
}
