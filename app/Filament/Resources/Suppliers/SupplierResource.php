<?php

namespace App\Filament\Resources\Suppliers;

use App\Filament\Resources\Suppliers\Pages\CreateSupplier;
use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'تامین‌کنندگان';

    protected static string|\UnitEnum|null $navigationGroup = 'خرید';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'تامین‌کننده';

    protected static ?string $pluralModelLabel = 'تامین‌کنندگان';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Supplier'))
                    ->description(__('Contact, payment, and compliance details for purchasing.'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('contact_name')
                            ->maxLength(255),
                        Select::make('status')
                            ->options(\App\Support\Admin\FilamentLocalization::options(Supplier::STATUS_OPTIONS))
                            ->native(false)
                            ->required()
                            ->default('active'),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('tax_number')
                            ->maxLength(255),
                        TextInput::make('payment_terms')
                            ->placeholder(__('Net 30, cash, bank transfer'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('address')
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
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->description(fn (Supplier $record): string => collect([$record->contact_name, $record->phone])->filter()->join(' · '))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(Supplier::STATUS_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('email')
                    ->placeholder(__('-'))
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('payment_terms')
                    ->placeholder(__('-'))
                    ->visibleFrom('lg'),
                TextColumn::make('purchase_orders_count')
                    ->label('POs')
                    ->counts('purchaseOrders')
                    ->visibleFrom('lg')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedBuildingStorefront)
            ->emptyStateHeading(__('No suppliers yet'))
            ->emptyStateDescription(__('Add suppliers before creating purchase orders.'))
            ->filters([
                SelectFilter::make('status')
                    ->options(\App\Support\Admin\FilamentLocalization::options(Supplier::STATUS_OPTIONS)),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
