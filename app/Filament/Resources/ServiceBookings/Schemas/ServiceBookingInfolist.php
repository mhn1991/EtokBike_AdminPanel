<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use App\Models\ServiceBooking;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder('-'),
                        TextEntry::make('customer_name'),
                        TextEntry::make('customer_phone')
                            ->placeholder('-'),
                        TextEntry::make('customer_email')
                            ->placeholder('-'),
                    ]),
                Section::make('Service request')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('service_type'),
                        TextEntry::make('bike_label')
                            ->placeholder('-'),
                        TextEntry::make('preferred_time')
                            ->placeholder('-'),
                        TextEntry::make('problem_description')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('Workshop status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ServiceBooking::STATUS_OPTIONS[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('admin_notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
                Section::make('Audit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
