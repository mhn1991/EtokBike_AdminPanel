<?php

namespace App\Filament\Resources\CustomerMessages;

use App\Filament\Resources\CustomerMessages\Pages\CreateCustomerMessage;
use App\Filament\Resources\CustomerMessages\Pages\EditCustomerMessage;
use App\Filament\Resources\CustomerMessages\Pages\ListCustomerMessages;
use App\Filament\Resources\CustomerMessages\Pages\ViewCustomerMessage;
use App\Filament\Resources\CustomerMessages\Schemas\CustomerMessageForm;
use App\Filament\Resources\CustomerMessages\Schemas\CustomerMessageInfolist;
use App\Filament\Resources\CustomerMessages\Tables\CustomerMessagesTable;
use App\Models\CustomerMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CustomerMessageResource extends Resource
{
    protected static ?string $model = CustomerMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Customer messages';

    protected static string|\UnitEnum|null $navigationGroup = 'Messages';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'customer message';

    protected static ?string $pluralModelLabel = 'customer messages';

    protected static ?string $recordTitleAttribute = 'label';

    public static function getNavigationBadge(): ?string
    {
        return number_format(CustomerMessage::query()->where('is_unread', true)->count());
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerMessageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerMessageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerMessagesTable::configure($table);
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
            'index' => ListCustomerMessages::route('/'),
            'create' => CreateCustomerMessage::route('/create'),
            'view' => ViewCustomerMessage::route('/{record}'),
            'edit' => EditCustomerMessage::route('/{record}/edit'),
        ];
    }
}
