<?php

namespace App\Filament\Resources\ServiceOfferings\Pages;

use App\Filament\Resources\ServiceOfferings\ServiceOfferingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceOffering extends EditRecord
{
    protected static string $resource = ServiceOfferingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
