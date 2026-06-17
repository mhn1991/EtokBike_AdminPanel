<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_number'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(Order::STATUS_OPTIONS[$state] ?? $state)),
                        TextEntry::make('payment_status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(Order::PAYMENT_STATUS_OPTIONS[$state] ?? $state)),
                        TextEntry::make('fulfillment_method')
                            ->formatStateUsing(fn (string $state): string => __(Order::FULFILLMENT_METHOD_OPTIONS[$state] ?? $state)),
                        TextEntry::make('placed_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                        TextEntry::make('stock_deducted_at')
                            ->label('Stock deducted')
                            ->dateTime()
                            ->placeholder(__('Not deducted yet')),
                        TextEntry::make('user.name')
                            ->label('Linked user')
                            ->placeholder(__('-')),
                    ]),
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('customer_name'),
                        TextEntry::make('customer_email')
                            ->placeholder(__('-')),
                        TextEntry::make('customer_phone')
                            ->placeholder(__('-')),
                        TextEntry::make('customer_notes')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Totals')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('currency'),
                        TextEntry::make('subtotal')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('discount_total')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('delivery_total')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('total')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                    ]),
                Section::make('Admin')
                    ->schema([
                        TextEntry::make('admin_notes')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder(__('-')),
                    ]),
            ]);
    }
}
