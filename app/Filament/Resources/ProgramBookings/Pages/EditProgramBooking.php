<?php

namespace App\Filament\Resources\ProgramBookings\Pages;

use App\Filament\Resources\ProgramBookings\ProgramBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProgramBooking extends EditRecord
{
    protected static string $resource = ProgramBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
