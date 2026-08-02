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
                Section::make(__('Program'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('program.title')
                            ->label('Program'),
                        TextEntry::make('program.date_label')
                            ->label('Date')
                            ->placeholder(__('-')),
                        TextEntry::make('attendees')
                            ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0)),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => __(ProgramBooking::STATUS_OPTIONS[$state] ?? $state))
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'cancelled' => 'danger',
                                'attended' => 'success',
                                'no_show' => 'gray',
                                default => 'gray',
                            }),
                    ]),
                Section::make(__('Customer'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder(__('-')),
                        TextEntry::make('customer_name'),
                        TextEntry::make('customer_phone')
                            ->placeholder(__('-')),
                        TextEntry::make('customer_email')
                            ->placeholder(__('-')),
                    ]),
                Section::make(__('Notes'))
                    ->schema([
                        TextEntry::make('customer_notes')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                        TextEntry::make('admin_notes')
                            ->placeholder(__('-'))
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Audit'))
                    ->columns(2)
                    ->schema([
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
