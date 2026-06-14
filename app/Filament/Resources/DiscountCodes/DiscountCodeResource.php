<?php

namespace App\Filament\Resources\DiscountCodes;

use App\Filament\Resources\DiscountCodes\Pages\CreateDiscountCode;
use App\Filament\Resources\DiscountCodes\Pages\EditDiscountCode;
use App\Filament\Resources\DiscountCodes\Pages\ListDiscountCodes;
use App\Models\DiscountCode;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Discounts';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Discount')
                ->description('Create coupon codes and campaign rules for checkout integration.')
                ->columns(3)
                ->schema([
                    TextInput::make('code')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('name')->required()->maxLength(255),
                    Select::make('type')->options(DiscountCode::TYPE_OPTIONS)->native(false)->required()->default('fixed'),
                    TextInput::make('value')->required()->integer()->minValue(0)->default(0),
                    TextInput::make('minimum_order_total')->required()->integer()->minValue(0)->suffix('IRR')->default(0),
                    TextInput::make('usage_limit')->integer()->minValue(1),
                    TextInput::make('used_count')->required()->integer()->minValue(0)->default(0),
                    DateTimePicker::make('starts_at')->seconds(false),
                    DateTimePicker::make('ends_at')->seconds(false),
                    Toggle::make('is_active')->default(true),
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false)->striped()->columns([
            TextColumn::make('code')->description(fn (DiscountCode $record): string => $record->name)->searchable()->sortable(),
            TextColumn::make('type')->badge()->formatStateUsing(fn (string $state): string => DiscountCode::TYPE_OPTIONS[$state] ?? $state)->sortable(),
            TextColumn::make('value')->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))->sortable(),
            TextColumn::make('used_count')->label('Used')->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))->visibleFrom('md')->sortable(),
            IconColumn::make('is_active')->label('Active')->boolean(),
            TextColumn::make('ends_at')->dateTime()->placeholder('-')->visibleFrom('lg')->sortable(),
        ])->emptyStateIcon(Heroicon::OutlinedReceiptPercent)
            ->emptyStateHeading('No discounts yet')
            ->emptyStateDescription('Create coupons for fixed discounts, percentage discounts, or free delivery.')
            ->filters([SelectFilter::make('type')->options(DiscountCode::TYPE_OPTIONS)])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscountCodes::route('/'),
            'create' => CreateDiscountCode::route('/create'),
            'edit' => EditDiscountCode::route('/{record}/edit'),
        ];
    }
}
