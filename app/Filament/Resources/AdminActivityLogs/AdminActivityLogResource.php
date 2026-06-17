<?php

namespace App\Filament\Resources\AdminActivityLogs;

use App\Filament\Resources\AdminActivityLogs\Pages\ListAdminActivityLogs;
use App\Models\AdminActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminActivityLogResource extends Resource
{
    protected static ?string $model = AdminActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'گزارش فعالیت';

    protected static string|\UnitEnum|null $navigationGroup = 'ممیزی';

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table->paginated(false)->striped()->columns([
            TextColumn::make('created_at')->label('Time')->dateTime()->sortable(),
            TextColumn::make('event')->searchable()->sortable(),
            TextColumn::make('user.name')->label('User')->placeholder(__('-'))->visibleFrom('md'),
            TextColumn::make('subject_type')->label('Subject')->description(fn (AdminActivityLog $record): string => $record->subject_id ? '#'.$record->subject_id : '')->placeholder(__('-'))->visibleFrom('lg'),
            TextColumn::make('ip_address')->placeholder(__('-'))->visibleFrom('xl'),
        ])->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList)
            ->emptyStateHeading(__('No activity yet'))
            ->emptyStateDescription(__('Admin actions can be recorded here for audit trails.'))
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminActivityLogs::route('/'),
        ];
    }
}
