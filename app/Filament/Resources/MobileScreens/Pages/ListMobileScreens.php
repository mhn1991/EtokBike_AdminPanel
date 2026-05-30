<?php

namespace App\Filament\Resources\MobileScreens\Pages;

use App\Filament\Resources\MobileScreens\MobileScreenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMobileScreens extends ListRecords
{
    protected static string $resource = MobileScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
