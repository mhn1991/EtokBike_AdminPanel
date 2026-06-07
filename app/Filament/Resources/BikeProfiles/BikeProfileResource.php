<?php

namespace App\Filament\Resources\BikeProfiles;

use App\Filament\Resources\BikeProfiles\Pages\CreateBikeProfile;
use App\Filament\Resources\BikeProfiles\Pages\EditBikeProfile;
use App\Filament\Resources\BikeProfiles\Pages\ListBikeProfiles;
use App\Models\BikeProfile;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BikeProfileResource extends Resource
{
    protected static ?string $model = BikeProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Bike profiles';

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'bike profile';

    protected static ?string $pluralModelLabel = 'bike profiles';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bike')
                    ->columns(3)
                    ->schema([
                        Select::make('customer_profile_id')
                            ->label('Customer')
                            ->relationship('customerProfile', 'name')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('frame_size')
                            ->maxLength(255),
                        TextInput::make('tire_size')
                            ->maxLength(255),
                        TextInput::make('brake_type')
                            ->maxLength(255),
                        TextInput::make('next_recommendation')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
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
                TextColumn::make('customerProfile.name')
                    ->label('Customer')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('title')
                    ->description(fn (BikeProfile $record): string => $record->subtitle)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('next_recommendation')
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBikeProfiles::route('/'),
            'create' => CreateBikeProfile::route('/create'),
            'edit' => EditBikeProfile::route('/{record}/edit'),
        ];
    }
}
