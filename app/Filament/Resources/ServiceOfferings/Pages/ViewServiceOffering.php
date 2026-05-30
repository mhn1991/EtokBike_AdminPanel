<?php

namespace App\Filament\Resources\ServiceOfferings\Pages;

use App\Filament\Resources\ServiceOfferings\ServiceOfferingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceOffering extends ViewRecord
{
    protected static string $resource = ServiceOfferingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
