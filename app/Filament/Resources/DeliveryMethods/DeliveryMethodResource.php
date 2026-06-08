<?php

namespace App\Filament\Resources\DeliveryMethods;

use App\Filament\Resources\DeliveryMethods\Pages\CreateDeliveryMethod;
use App\Filament\Resources\DeliveryMethods\Pages\EditDeliveryMethod;
use App\Filament\Resources\DeliveryMethods\Pages\ListDeliveryMethods;
use App\Models\DeliveryMethod;
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

class DeliveryMethodResource extends Resource
{
    protected static ?string $model = DeliveryMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Delivery methods';

    protected static string|\UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'delivery method';

    protected static ?string $pluralModelLabel = 'delivery methods';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Delivery method')
                    ->description('Shown as cards on the mobile cart page.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('price_label')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
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
                TextColumn::make('title')
                    ->description(fn (DeliveryMethod $record): string => $record->subtitle)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price_label')
                    ->placeholder('-'),
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
            'index' => ListDeliveryMethods::route('/'),
            'create' => CreateDeliveryMethod::route('/create'),
            'edit' => EditDeliveryMethod::route('/{record}/edit'),
        ];
    }
}
