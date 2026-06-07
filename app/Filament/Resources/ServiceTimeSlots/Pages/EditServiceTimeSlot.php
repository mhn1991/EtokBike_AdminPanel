<?php

namespace App\Filament\Resources\ServiceTimeSlots\Pages;

use App\Filament\Resources\ServiceTimeSlots\ServiceTimeSlotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceTimeSlot extends EditRecord
{
    protected static string $resource = ServiceTimeSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
