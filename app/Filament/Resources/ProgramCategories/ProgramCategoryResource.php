<?php

namespace App\Filament\Resources\ProgramCategories;

use App\Filament\Resources\ProgramCategories\Pages\CreateProgramCategory;
use App\Filament\Resources\ProgramCategories\Pages\EditProgramCategory;
use App\Filament\Resources\ProgramCategories\Pages\ListProgramCategories;
use App\Filament\Resources\ProgramCategories\Schemas\ProgramCategoryForm;
use App\Filament\Resources\ProgramCategories\Tables\ProgramCategoriesTable;
use App\Models\ProgramCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProgramCategoryResource extends Resource
{
    protected static ?string $model = ProgramCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Program categories';

    protected static string|\UnitEnum|null $navigationGroup = 'Programs';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'program category';

    protected static ?string $pluralModelLabel = 'program categories';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return ProgramCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramCategoriesTable::configure($table);
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
            'index' => ListProgramCategories::route('/'),
            'create' => CreateProgramCategory::route('/create'),
            'edit' => EditProgramCategory::route('/{record}/edit'),
        ];
    }
}
