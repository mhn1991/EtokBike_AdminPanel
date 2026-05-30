<?php

namespace App\Filament\Resources\MobileScreens;

use App\Filament\Resources\MobileScreens\Pages\CreateMobileScreen;
use App\Filament\Resources\MobileScreens\Pages\EditMobileScreen;
use App\Filament\Resources\MobileScreens\Pages\ListMobileScreens;
use App\Filament\Resources\MobileScreens\Pages\ViewMobileScreen;
use App\Filament\Resources\MobileScreens\RelationManagers\SectionsRelationManager;
use App\Filament\Resources\MobileScreens\Schemas\MobileScreenForm;
use App\Filament\Resources\MobileScreens\Schemas\MobileScreenInfolist;
use App\Filament\Resources\MobileScreens\Tables\MobileScreensTable;
use App\Models\MobileScreen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MobileScreenResource extends Resource
{
    protected static ?string $model = MobileScreen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = 'App pages';

    protected static string|\UnitEnum|null $navigationGroup = 'Mobile App';

    protected static ?string $modelLabel = 'app page';

    protected static ?string $pluralModelLabel = 'app pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return MobileScreenForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MobileScreenInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MobileScreensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMobileScreens::route('/'),
            'create' => CreateMobileScreen::route('/create'),
            'view' => ViewMobileScreen::route('/{record}'),
            'edit' => EditMobileScreen::route('/{record}/edit'),
        ];
    }
}
