<?php

namespace App\Filament\Resources\MobileScreens\Pages;

use App\Filament\Resources\MobileScreens\MobileScreenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMobileScreen extends EditRecord
{
    protected static string $resource = MobileScreenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
