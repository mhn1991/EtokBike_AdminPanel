<?php

namespace App\Filament\Resources\ServiceBookings\Schemas;

use App\Models\ServiceBooking;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServiceBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('customer_phone')
                    ->tel(),
                TextInput::make('customer_email')
                    ->email(),
                TextInput::make('service_type')
                    ->required(),
                TextInput::make('bike_label'),
                TextInput::make('preferred_time'),
                Textarea::make('problem_description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(ServiceBooking::STATUS_OPTIONS)
                    ->required()
                    ->default('pending'),
                Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }
}
