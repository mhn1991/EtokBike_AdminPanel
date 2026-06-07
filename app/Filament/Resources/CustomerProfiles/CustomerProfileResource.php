<?php

namespace App\Filament\Resources\CustomerProfiles;

use App\Filament\Resources\CustomerProfiles\Pages\CreateCustomerProfile;
use App\Filament\Resources\CustomerProfiles\Pages\EditCustomerProfile;
use App\Filament\Resources\CustomerProfiles\Pages\ListCustomerProfiles;
use App\Models\CustomerProfile;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerProfileResource extends Resource
{
    protected static ?string $model = CustomerProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Customer profiles';

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'customer profile';

    protected static ?string $pluralModelLabel = 'customer profiles';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('Linked user')
                            ->relationship('user', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make('Delivery and notes')
                    ->schema([
                        Textarea::make('delivery_address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->description(fn (CustomerProfile $record): ?string => $record->phone)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('delivery_address')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerProfiles::route('/'),
            'create' => CreateCustomerProfile::route('/create'),
            'edit' => EditCustomerProfile::route('/{record}/edit'),
        ];
    }
}
