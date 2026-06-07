<?php

namespace App\Filament\Resources\StoreProfiles;

use App\Filament\Resources\StoreProfiles\Pages\CreateStoreProfile;
use App\Filament\Resources\StoreProfiles\Pages\EditStoreProfile;
use App\Filament\Resources\StoreProfiles\Pages\ListStoreProfiles;
use App\Models\StoreProfile;
use BackedEnum;
use Filament\Actions\EditAction;
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

class StoreProfileResource extends Resource
{
    protected static ?string $model = StoreProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Store profile';

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'store profile';

    protected static ?string $pluralModelLabel = 'store profiles';

    protected static ?string $recordTitleAttribute = 'branch_title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Current status')
                    ->description('Shown in the Home screen store status block.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('status_title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('status_subtitle')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('status_label')
                            ->maxLength(255),
                        Textarea::make('status_description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Store info')
                    ->description('Shown in the Home screen store info block.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('branch_title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('action_label')
                            ->maxLength(255),
                        TextInput::make('address')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('hours')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('branch_title')
                    ->description(fn (StoreProfile $record): string => $record->address)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status_title')
                    ->searchable(),
                TextColumn::make('hours')
                    ->toggleable(),
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
            'index' => ListStoreProfiles::route('/'),
            'create' => CreateStoreProfile::route('/create'),
            'edit' => EditStoreProfile::route('/{record}/edit'),
        ];
    }
}
