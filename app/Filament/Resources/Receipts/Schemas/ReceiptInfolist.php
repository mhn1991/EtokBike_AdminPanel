<?php

namespace App\Filament\Resources\Receipts\Schemas;

use App\Models\Receipt;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReceiptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Receipt')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('receipt_number'),
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(Receipt::TYPE_OPTIONS[$state] ?? $state)),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(Receipt::STATUS_OPTIONS[$state] ?? $state)),
                        TextEntry::make('order.order_number')
                            ->label('Order')
                            ->placeholder(__('-')),
                        TextEntry::make('returnRequest.return_number')
                            ->label('Return')
                            ->placeholder(__('-')),
                        TextEntry::make('issued_at')
                            ->dateTime()
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
                        TextEntry::make('billing_address')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Totals')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('subtotal')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('discount_total')
                            ->label('Discount')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('delivery_total')
                            ->label('Delivery')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('tax_total')
                            ->label('Tax')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('total')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                    ]),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
