<?php

namespace App\Filament\Resources\ServiceBookings\Pages;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceBooking extends ViewRecord
{
    protected static string $resource = ServiceBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
