<?php

namespace App\Filament\Resources\TaxRates;

use App\Filament\Resources\TaxRates\Pages\CreateTaxRate;
use App\Filament\Resources\TaxRates\Pages\EditTaxRate;
use App\Filament\Resources\TaxRates\Pages\ListTaxRates;
use App\Models\TaxRate;
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

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Tax rates';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tax rate')
                ->description('Define VAT/tax rates for future order and invoice calculations.')
                ->columns(3)
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('rate')->required()->numeric()->minValue(0)->maxValue(100)->suffix('%')->default(0),
                    Toggle::make('is_inclusive')->label('Prices include tax')->default(false),
                    Toggle::make('is_active')->default(true),
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false)->striped()->columns([
            TextColumn::make('name')->description(fn (TaxRate $record): string => $record->code)->searchable()->sortable(),
            TextColumn::make('rate')->formatStateUsing(fn ($state): string => number_format((float) $state, 2).'%')->sortable(),
            IconColumn::make('is_inclusive')->label('Inclusive')->boolean(),
            IconColumn::make('is_active')->label('Active')->boolean(),
        ])->emptyStateIcon(Heroicon::OutlinedReceiptPercent)
            ->emptyStateHeading('No tax rates yet')
            ->emptyStateDescription('Create tax/VAT rates before applying tax to invoices.')
            ->defaultSort('name')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxRates::route('/'),
            'create' => CreateTaxRate::route('/create'),
            'edit' => EditTaxRate::route('/{record}/edit'),
        ];
    }
}
