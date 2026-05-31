<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->description('Track fulfilment, payment, and the linked customer account.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('order_number')
                            ->label('Order number')
                            ->helperText('Leave blank to auto-generate on create.')
                            ->maxLength(255),
                        ToggleButtons::make('status')
                            ->options(Order::STATUS_OPTIONS)
                            ->colors([
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'processing' => 'info',
                                'ready' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpan(2)
                            ->default('pending'),
                        ToggleButtons::make('payment_status')
                            ->options(Order::PAYMENT_STATUS_OPTIONS)
                            ->colors([
                                'unpaid' => 'warning',
                                'paid' => 'success',
                                'refunded' => 'danger',
                                'failed' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('unpaid'),
                        ToggleButtons::make('fulfillment_method')
                            ->options(Order::FULFILLMENT_METHOD_OPTIONS)
                            ->colors([
                                'pickup' => 'info',
                                'delivery' => 'primary',
                            ])
                            ->inline()
                            ->required()
                            ->default('pickup'),
                        DateTimePicker::make('placed_at')
                            ->seconds(false)
                            ->helperText('Defaults to the current time when left blank.'),
                        Select::make('user_id')
                            ->label('Linked user')
                            ->relationship('user', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Customer')
                    ->description('Details used by staff to contact the buyer.')
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
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Totals')
                    ->description('Subtotal is calculated from order items; discounts and delivery adjust the final total.')
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
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('discount_total')
                            ->label('Discount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('delivery_total')
                            ->label('Delivery')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('IRR')
                            ->default(0),
                        TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->suffix('IRR')
                            ->default(0),
                    ]),
                Section::make('Admin notes')
                    ->schema([
                        Textarea::make('admin_notes')
                            ->hiddenLabel()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
