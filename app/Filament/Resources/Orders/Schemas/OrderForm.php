<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(3)
                    ->schema([
                        TextInput::make('order_number')
                            ->helperText('Leave blank to auto-generate on create.')
                            ->maxLength(255),
                        Select::make('status')
                            ->options(Order::STATUS_OPTIONS)
                            ->required()
                            ->default('pending'),
                        Select::make('payment_status')
                            ->options(Order::PAYMENT_STATUS_OPTIONS)
                            ->required()
                            ->default('unpaid'),
                        Select::make('fulfillment_method')
                            ->options(Order::FULFILLMENT_METHOD_OPTIONS)
                            ->required()
                            ->default('pickup'),
                        DateTimePicker::make('placed_at')
                            ->seconds(false),
                        Select::make('user_id')
                            ->label('Linked user')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
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
                        Textarea::make('customer_notes')
                            ->columnSpanFull(),
                    ]),
                Section::make('Totals')
                    ->columns(4)
                    ->schema([
                        TextInput::make('currency')
                            ->required()
                            ->maxLength(8)
                            ->default('IRR'),
                        TextInput::make('subtotal')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                        TextInput::make('discount_total')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('delivery_total')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),
                    ]),
                Section::make('Admin notes')
                    ->schema([
                        Textarea::make('admin_notes')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
