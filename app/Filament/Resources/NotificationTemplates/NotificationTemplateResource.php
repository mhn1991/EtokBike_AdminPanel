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

    protected static ?string $navigationLabel = 'Templates';

    protected static string|\UnitEnum|null $navigationGroup = 'Notifications';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Template')
                ->description('Reusable notification copy for email, SMS, WhatsApp, or push integrations.')
                ->columns(3)
                ->schema([
                    TextInput::make('key')->required()->maxLength(255)->unique(ignoreRecord: true),
                    Select::make('channel')->options(NotificationTemplate::CHANNEL_OPTIONS)->native(false)->required()->default('email'),
                    Toggle::make('is_active')->default(true),
                    TextInput::make('subject')->maxLength(255)->columnSpanFull(),
                    Textarea::make('body')->required()->rows(8)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->paginated(false)->striped()->columns([
            TextColumn::make('key')->searchable()->sortable(),
            TextColumn::make('channel')->badge()->formatStateUsing(fn (string $state): string => NotificationTemplate::CHANNEL_OPTIONS[$state] ?? $state)->sortable(),
            TextColumn::make('subject')->placeholder('-')->wrap()->visibleFrom('md'),
            IconColumn::make('is_active')->label('Active')->boolean(),
            TextColumn::make('logs_count')->label('Logs')->counts('logs')->visibleFrom('lg')->sortable(),
        ])->emptyStateIcon(Heroicon::OutlinedBellAlert)
            ->emptyStateHeading('No notification templates yet')
            ->emptyStateDescription('Create templates for order confirmation, receipt delivery, low-stock alerts, and shipment updates.')
            ->filters([SelectFilter::make('channel')->options(NotificationTemplate::CHANNEL_OPTIONS)])
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
