<?php

namespace App\Filament\Resources\ContentPages;

use App\Filament\Resources\ContentPages\Pages\CreateContentPage;
use App\Filament\Resources\ContentPages\Pages\EditContentPage;
use App\Filament\Resources\ContentPages\Pages\ListContentPages;
use App\Models\ContentPage;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ContentPageResource extends Resource
{
    protected static ?string $model = ContentPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Content pages';

    protected static string|\UnitEnum|null $navigationGroup = 'SEO';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page')
                ->description('Create SEO-managed storefront content pages.')
                ->columns(3)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        })
                        ->maxLength(255),
                    TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('sort_order')->required()->integer()->minValue(0)->default(0),
                    Textarea::make('excerpt')->rows(3)->maxLength(500)->columnSpanFull(),
                    Textarea::make('body')->rows(10)->columnSpanFull(),
                    Toggle::make('is_active')->default(true),
                ]),
            Section::make('SEO')
                ->columns(3)
                ->schema([
                    TextInput::make('seo_title')->maxLength(255)->columnSpanFull(),
                    Textarea::make('seo_description')->rows(3)->maxLength(500)->columnSpanFull(),
                    TextInput::make('canonical_url')->maxLength(255),
                    Select::make('robots')->options(ContentPage::ROBOTS_OPTIONS)->native(false)->required()->default('index,follow'),
                    Toggle::make('include_in_sitemap')->default(true),
                    FileUpload::make('og_image')
                        ->label('Social image')
                        ->disk('public')
                        ->directory('seo/pages')
                        ->visibility('public')
                        ->image()
                        ->imagePreviewHeight('140')
                        ->openable()
                        ->downloadable(),
                    TextInput::make('og_title')->maxLength(255),
                    Textarea::make('og_description')->rows(3)->maxLength(500),
                    TextInput::make('sitemap_priority')->numeric()->minValue(0)->maxValue(1)->default(0.6),
                    Select::make('sitemap_change_frequency')->options(ContentPage::CHANGE_FREQUENCY_OPTIONS)->native(false)->required()->default('monthly'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('title')->description(fn (ContentPage $record): string => '/pages/'.$record->slug)->searchable()->sortable()->wrap(),
                TextColumn::make('robots')->badge()->visibleFrom('md'),
                IconColumn::make('include_in_sitemap')->label('Sitemap')->boolean()->visibleFrom('lg'),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->visibleFrom('xl')->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateHeading('No content pages yet')
            ->emptyStateDescription('Create policy, FAQ, about, and landing pages with SEO metadata.')
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentPages::route('/'),
            'create' => CreateContentPage::route('/create'),
            'edit' => EditContentPage::route('/{record}/edit'),
        ];
    }
}
