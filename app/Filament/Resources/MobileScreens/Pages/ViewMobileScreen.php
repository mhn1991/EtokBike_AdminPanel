<?php

namespace App\Filament\Resources\MobileScreens\Pages;

use App\Filament\Resources\MobileScreens\MobileScreenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMobileScreen extends ViewRecord
{
    protected static string $resource = MobileScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
