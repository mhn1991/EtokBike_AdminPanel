<?php

namespace App\Filament\Resources\ServiceBookings\Pages;

use App\Filament\Resources\ServiceBookings\ServiceBookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceBooking extends CreateRecord
{
    protected static string $resource = ServiceBookingResource::class;
}
