<?php

namespace App\Filament\Resources\StoreProfiles\Pages;

use App\Filament\Resources\StoreProfiles\StoreProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStoreProfile extends EditRecord
{
    protected static string $resource = StoreProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
