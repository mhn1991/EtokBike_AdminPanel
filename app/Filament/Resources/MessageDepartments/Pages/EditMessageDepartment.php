<?php

namespace App\Filament\Resources\MessageDepartments\Pages;

use App\Filament\Resources\MessageDepartments\MessageDepartmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMessageDepartment extends EditRecord
{
    protected static string $resource = MessageDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
