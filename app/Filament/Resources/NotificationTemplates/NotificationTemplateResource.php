<?php

namespace App\Filament\Resources\NotificationTemplates;

use App\Filament\Resources\NotificationTemplates\Pages\CreateNotificationTemplate;
use App\Filament\Resources\NotificationTemplates\Pages\EditNotificationTemplate;
use App\Filament\Resources\NotificationTemplates\Pages\ListNotificationTemplates;
use App\Models\NotificationTemplate;
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

class NotificationTemplateResource extends Resource
{
    protected static ?string $model = NotificationTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'قالب‌ها';

    protected static string|\UnitEnum|null $navigationGroup = 'اعلان‌ها';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'قالب اعلان';

    protected static ?string $pluralModelLabel = 'قالب‌های اعلان';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Template'))
                ->description(__('Reusable notification copy for email, SMS, WhatsApp, or push integrations.'))
                ->columns(3)
                ->schema([
                    TextInput::make('key')->required()->maxLength(255)->unique(ignoreRecord: true),
                    Select::make('channel')->options(\App\Support\Admin\FilamentLocalization::options(NotificationTemplate::CHANNEL_OPTIONS))->native(false)->required()->default('email'),
                    Toggle::make('is_active')->default(true),
                    TextInput::make('subject')->maxLength(255)->columnSpanFull(),
                    Textarea::make('body')->required()->rows(8)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->striped()->columns([
            TextColumn::make('key')->searchable()->sortable(),
            TextColumn::make('channel')->badge()->formatStateUsing(fn (string $state): string => __(NotificationTemplate::CHANNEL_OPTIONS[$state] ?? $state))->sortable(),
            TextColumn::make('subject')->placeholder(__('-'))->wrap()->visibleFrom('md'),
            IconColumn::make('is_active')->label('Active')->boolean(),
            TextColumn::make('logs_count')->label('لاگ‌ها')->counts('logs')->visibleFrom('lg')->sortable(),
        ])->emptyStateIcon(Heroicon::OutlinedBellAlert)
            ->emptyStateHeading(__('No notification templates yet'))
            ->emptyStateDescription(__('Create templates for order confirmation, receipt delivery, low-stock alerts, and shipment updates.'))
            ->filters([SelectFilter::make('channel')->options(\App\Support\Admin\FilamentLocalization::options(NotificationTemplate::CHANNEL_OPTIONS))])
            ->defaultSort('key')
            ->recordActions([EditAction::make()])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationTemplates::route('/'),
            'create' => CreateNotificationTemplate::route('/create'),
            'edit' => EditNotificationTemplate::route('/{record}/edit'),
        ];
    }
}
