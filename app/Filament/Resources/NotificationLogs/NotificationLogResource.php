<?php

namespace App\Filament\Resources\NotificationLogs;

use App\Filament\Resources\NotificationLogs\Pages\CreateNotificationLog;
use App\Filament\Resources\NotificationLogs\Pages\EditNotificationLog;
use App\Filament\Resources\NotificationLogs\Pages\ListNotificationLogs;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'لاگ‌ها';

    protected static string|\UnitEnum|null $navigationGroup = 'اعلان‌ها';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'لاگ اعلان';

    protected static ?string $pluralModelLabel = 'لاگ‌های اعلان';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Notification log'))
                ->description(__('Track sent, failed, and pending notification attempts.'))
                ->columns(3)
                ->schema([
                    Select::make('notification_template_id')->label('Template')->relationship('template', 'key')->native(false)->searchable()->preload(),
                    Select::make('order_id')->label('Order')->relationship('order', 'order_number')->native(false)->searchable()->preload(),
                    Select::make('channel')->options(\App\Support\Admin\FilamentLocalization::options(NotificationTemplate::CHANNEL_OPTIONS))->native(false)->required()->default('email'),
                    TextInput::make('recipient')->required()->maxLength(255),
                    ToggleButtons::make('status')->options(\App\Support\Admin\FilamentLocalization::options(NotificationLog::STATUS_OPTIONS))->colors([
                        'pending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        'cancelled' => 'gray',
                    ])->inline()->required()->default('pending'),
                    DateTimePicker::make('sent_at')->seconds(false),
                    TextInput::make('subject')->maxLength(255)->columnSpanFull(),
                    Textarea::make('body')->rows(5)->columnSpanFull(),
                    Textarea::make('failure_reason')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->striped()->columns([
            TextColumn::make('recipient')->description(fn (NotificationLog $record): string => collect([$record->channel, $record->order?->order_number])->filter()->join(' · '))->searchable()->wrap(),
            TextColumn::make('status')->badge()->formatStateUsing(fn (string $state): string => __(NotificationLog::STATUS_OPTIONS[$state] ?? $state))->color(fn (string $state): string => match ($state) {
                'sent' => 'success',
                'failed' => 'danger',
                'cancelled' => 'gray',
                default => 'warning',
            })->sortable(),
            TextColumn::make('subject')->placeholder(__('-'))->visibleFrom('md')->wrap(),
            TextColumn::make('sent_at')->dateTime()->placeholder(__('-'))->visibleFrom('lg')->sortable(),
            TextColumn::make('created_at')->dateTime()->visibleFrom('xl')->sortable(),
        ])->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->emptyStateHeading(__('No notification logs yet'))
            ->emptyStateDescription(__('Notification attempts will be tracked here once integrations are enabled.'))
            ->filters([SelectFilter::make('status')->options(\App\Support\Admin\FilamentLocalization::options(NotificationLog::STATUS_OPTIONS))])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->label('Bulk actions'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationLogs::route('/'),
            'create' => CreateNotificationLog::route('/create'),
            'edit' => EditNotificationLog::route('/{record}/edit'),
        ];
    }
}
