<?php

namespace App\Filament\Resources\ServiceTimeSlots;

use App\Filament\Resources\ServiceTimeSlots\Pages\CreateServiceTimeSlot;
use App\Filament\Resources\ServiceTimeSlots\Pages\EditServiceTimeSlot;
use App\Filament\Resources\ServiceTimeSlots\Pages\ListServiceTimeSlots;
use App\Models\ServiceTimeSlot;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceTimeSlotResource extends Resource
{
    protected static ?string $model = ServiceTimeSlot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Service time slots';

    protected static string|\UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'service time slot';

    protected static ?string $pluralModelLabel = 'service time slots';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Time slot')
                    ->columns(3)
                    ->schema([
                        TextInput::make('label')
                            ->required()
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
                TextColumn::make('label')
                    ->searchable()
                    ->sortable(),
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
            'index' => ListServiceTimeSlots::route('/'),
            'create' => CreateServiceTimeSlot::route('/create'),
            'edit' => EditServiceTimeSlot::route('/{record}/edit'),
        ];
    }
}
