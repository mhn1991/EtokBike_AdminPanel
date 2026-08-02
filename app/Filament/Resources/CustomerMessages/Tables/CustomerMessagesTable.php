<?php

namespace App\Filament\Resources\CustomerMessages\Tables;

use App\Models\CustomerMessage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CustomerMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('department.title')
                    ->label('Department')
                    ->sortable()
                    ->wrap()
                    ->extraCellAttributes(['dir' => 'rtl']),
                TextColumn::make('user.name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sender')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(CustomerMessage::SENDER_OPTIONS[$state] ?? $state))
                    ->color(fn (string $state): string => $state === 'client' ? 'warning' : 'info')
                    ->visibleFrom('md')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable()
                    ->visibleFrom('md')
                    ->extraCellAttributes(['dir' => 'rtl']),
                TextColumn::make('text')
                    ->limit(90)
                    ->lineClamp(2)
                    ->searchable()
                    ->wrap()
                    ->extraCellAttributes(['dir' => 'rtl']),
                TextColumn::make('time_label')
                    ->label('Time')
                    ->visibleFrom('lg')
                    ->searchable(),
                IconColumn::make('is_unread')
                    ->label('Needs response')
                    ->visibleFrom('md')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->emptyStateHeading(__('No customer messages yet'))
            ->emptyStateDescription(__('Incoming and department messages will appear here.'))
            ->filters([
                SelectFilter::make('message_department_id')
                    ->label('Department')
                    ->relationship('department', 'title'),
                SelectFilter::make('sender')
                    ->options(\App\Support\Admin\FilamentLocalization::options(CustomerMessage::SENDER_OPTIONS)),
                TernaryFilter::make('is_unread')
                    ->label('Needs response'),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query->orderByDesc('is_unread')->orderByDesc('created_at'))
            ->recordActions(ActionGroup::make([
                ViewAction::make()
                    ->label('Open thread'),
                EditAction::make()
                    ->label('Edit message'),
                Action::make('markReplied')
                    ->label('Mark replied')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->visible(fn (CustomerMessage $record): bool => (bool) $record->is_unread)
                    ->action(fn (CustomerMessage $record) => $record->update(['is_unread' => false]))
                    ->successNotificationTitle(__('Message marked replied')),
                Action::make('markNeedsResponse')
                    ->label('Needs response')
                    ->icon(Heroicon::BellAlert)
                    ->color('warning')
                    ->visible(fn (CustomerMessage $record): bool => ! $record->is_unread)
                    ->action(fn (CustomerMessage $record) => $record->update(['is_unread' => true]))
                    ->successNotificationTitle(__('Message marked as needing response')),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkMarkReplied')
                        ->label('Mark replied')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each(fn (CustomerMessage $record) => $record->update(['is_unread' => false])))
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle(__('Selected messages marked replied')),
                ])->label('Bulk actions'),
            ]);
    }
}
