<?php

namespace App\Filament\Resources\MessageDepartments;

use App\Filament\Resources\MessageDepartments\Pages\CreateMessageDepartment;
use App\Filament\Resources\MessageDepartments\Pages\EditMessageDepartment;
use App\Filament\Resources\MessageDepartments\Pages\ListMessageDepartments;
use App\Filament\Resources\MessageDepartments\Schemas\MessageDepartmentForm;
use App\Filament\Resources\MessageDepartments\Tables\MessageDepartmentsTable;
use App\Models\MessageDepartment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MessageDepartmentResource extends Resource
{
    protected static ?string $model = MessageDepartment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?string $navigationLabel = 'Message departments';

    protected static string|\UnitEnum|null $navigationGroup = 'Messages';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'message department';

    protected static ?string $pluralModelLabel = 'message departments';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return MessageDepartmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MessageDepartmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMessageDepartments::route('/'),
            'create' => CreateMessageDepartment::route('/create'),
            'edit' => EditMessageDepartment::route('/{record}/edit'),
        ];
    }
}
