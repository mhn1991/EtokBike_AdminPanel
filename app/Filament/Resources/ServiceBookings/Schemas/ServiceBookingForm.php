<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use App\Models\ServiceBooking;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->description('Contact details for service follow-up.')
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
                Section::make('Service request')
                    ->description('What the customer needs and when they prefer to visit.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('service_type')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('bike_label')
                            ->maxLength(255),
                        TextInput::make('preferred_time')
                            ->maxLength(255),
                        Textarea::make('problem_description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Workshop status')
                    ->columns(2)
                    ->schema([
                        ToggleButtons::make('status')
                            ->options(ServiceBooking::STATUS_OPTIONS)
                            ->colors([
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpanFull()
                            ->default('pending'),
                        Textarea::make('admin_notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
