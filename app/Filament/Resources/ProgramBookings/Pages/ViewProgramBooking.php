<?php

namespace App\Filament\Resources\ProgramBookings\Pages;

use App\Filament\Resources\ProgramBookings\ProgramBookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramBooking extends ViewRecord
{
    protected static string $resource = ProgramBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
