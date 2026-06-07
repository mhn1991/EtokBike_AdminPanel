<?php

namespace App\Filament\Resources\ServiceTimeSlots\Pages;

use App\Filament\Resources\ServiceTimeSlots\ServiceTimeSlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceTimeSlots extends ListRecords
{
    protected static string $resource = ServiceTimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
