<?php

namespace App\Filament\Resources\ProgramBookings\Pages;

use App\Filament\Resources\ProgramBookings\ProgramBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProgramBookings extends ListRecords
{
    protected static string $resource = ProgramBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
