<?php

namespace App\Filament\Resources\BikeProfiles\Pages;

use App\Filament\Resources\BikeProfiles\BikeProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBikeProfiles extends ListRecords
{
    protected static string $resource = BikeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
