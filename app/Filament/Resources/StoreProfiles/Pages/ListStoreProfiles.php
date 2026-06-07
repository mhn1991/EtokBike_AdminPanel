<?php

namespace App\Filament\Resources\StoreProfiles\Pages;

use App\Filament\Resources\StoreProfiles\StoreProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStoreProfiles extends ListRecords
{
    protected static string $resource = StoreProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
