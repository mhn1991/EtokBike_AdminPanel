<?php

namespace App\Filament\Resources\ProgramBookings\Schemas;

use App\Models\ProgramBooking;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramBookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('program.title')
                            ->label('Program'),
                        TextEntry::make('program.date_label')
                            ->label('Date')
                            ->placeholder('-'),
                        TextEntry::make('attendees')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ProgramBooking::STATUS_OPTIONS[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'cancelled' => 'danger',
                                'attended' => 'success',
                                'no_show' => 'gray',
                                default => 'gray',
                            }),
                    ]),
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
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('customer_notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
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
