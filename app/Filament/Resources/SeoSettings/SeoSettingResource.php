<?php

namespace App\Filament\Resources\SeoSettings;

use App\Filament\Resources\SeoSettings\Pages\CreateSeoSetting;
use App\Filament\Resources\SeoSettings\Pages\EditSeoSetting;
use App\Filament\Resources\SeoSettings\Pages\ListSeoSettings;
use App\Models\SeoSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
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

class SeoSettingResource extends Resource
{
    protected static ?string $model = SeoSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = 'SEO settings';

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Defaults')
                ->description('Fallback metadata used when a page, category, or product does not define its own SEO fields.')
                ->columns(2)
                ->schema([
                    TextInput::make('site_name')->required()->maxLength(255)->default('EtokBike'),
                    Toggle::make('is_active')->default(true),
                    TextInput::make('default_title')->maxLength(255)->columnSpanFull(),
                    Textarea::make('default_description')->rows(3)->maxLength(500)->columnSpanFull(),
                    FileUpload::make('default_og_image')
                        ->label('Default social image')
                        ->disk('public')
                        ->directory('seo')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('140')
                        ->openable()
                        ->downloadable(),
                    TextInput::make('twitter_handle')->placeholder('@etokbike')->maxLength(255),
                    KeyValue::make('social_profiles')
                        ->keyLabel('Network')
                        ->valueLabel('URL')
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
                TextColumn::make('site_name')->searchable()->sortable(),
                TextColumn::make('default_title')->placeholder('-')->wrap(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable()->visibleFrom('lg'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedMagnifyingGlass)
            ->emptyStateHeading('No SEO settings yet')
            ->emptyStateDescription('Create one active settings record for site-wide SEO defaults.')
            ->defaultSort('updated_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoSettings::route('/'),
            'create' => CreateSeoSetting::route('/create'),
            'edit' => EditSeoSetting::route('/{record}/edit'),
        ];
    }
}
