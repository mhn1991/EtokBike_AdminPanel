<?php

namespace App\Filament\Resources\ProgramBookings\Schemas;

use App\Models\ProgramBooking;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program')
                    ->description('The ride, class, or event the customer wants to attend.')
                    ->columns(3)
                    ->schema([
                        Select::make('program_id')
                            ->label('Program')
                            ->relationship('program', 'title')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('attendees')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(1),
                        ToggleButtons::make('status')
                            ->options(ProgramBooking::STATUS_OPTIONS)
                            ->colors([
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'cancelled' => 'danger',
                                'attended' => 'success',
                                'no_show' => 'gray',
                            ])
                            ->inline()
                            ->required()
                            ->default('pending'),
                    ]),
                Section::make('Customer')
                    ->description('Contact details used to confirm attendance and meeting point.')
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('Linked user')
                            ->relationship('user', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                    ]),
                Section::make('Notes')
                    ->schema([
                        Textarea::make('customer_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('admin_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
