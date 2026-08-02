<?php

namespace App\Filament\Resources\SeoRedirects;

use App\Filament\Resources\SeoRedirects\Pages\CreateSeoRedirect;
use App\Filament\Resources\SeoRedirects\Pages\EditSeoRedirect;
use App\Filament\Resources\SeoRedirects\Pages\ListSeoRedirects;
use App\Models\SeoRedirect;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeoRedirectResource extends Resource
{
    protected static ?string $model = SeoRedirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'ریدایرکت‌ها';

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ریدایرکت';

    protected static ?string $pluralModelLabel = 'ریدایرکت‌ها';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Redirect'))
                ->description(__('Redirect an old storefront URL to a current page.'))
                ->columns(3)
                ->schema([
                    TextInput::make('source_path')->placeholder(__('/old-page'))->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('target_url')->placeholder(__('/shop or https://example.com/new'))->required()->maxLength(255),
                    Select::make('status_code')->options(\App\Support\Admin\FilamentLocalization::options(SeoRedirect::STATUS_CODE_OPTIONS))->native(false)->required()->default(301),
                    Toggle::make('is_active')->default(true),
                    Textarea::make('notes')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('source_path')->searchable()->sortable(),
                TextColumn::make('target_url')->searchable()->wrap(),
                TextColumn::make('status_code')->badge()->sortable(),
                TextColumn::make('hit_count')->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))->sortable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_hit_at')->dateTime()->placeholder(__('-'))->visibleFrom('lg')->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedArrowPath)
            ->emptyStateHeading(__('No redirects yet'))
            ->emptyStateDescription(__('Create redirects when product, category, or content URLs change.'))
            ->filters([
                SelectFilter::make('status_code')->options(\App\Support\Admin\FilamentLocalization::options(SeoRedirect::STATUS_CODE_OPTIONS)),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoRedirects::route('/'),
            'create' => CreateSeoRedirect::route('/create'),
            'edit' => EditSeoRedirect::route('/{record}/edit'),
        ];
    }
}
