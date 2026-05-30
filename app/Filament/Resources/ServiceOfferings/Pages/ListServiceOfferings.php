<?php

namespace App\Filament\Resources\ServiceOfferings\Pages;

use App\Filament\Resources\ServiceOfferings\ServiceOfferingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceOfferings extends ListRecords
{
    protected static string $resource = ServiceOfferingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
