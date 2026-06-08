<?php

namespace App\Filament\Resources\CustomerMessages\Tables;

use App\Models\CustomerMessage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomerMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
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
                    ->formatStateUsing(fn (string $state): string => CustomerMessage::SENDER_OPTIONS[$state] ?? $state)
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
            ->emptyStateHeading('No customer messages yet')
            ->emptyStateDescription('Incoming and department messages will appear here.')
            ->filters([
                SelectFilter::make('message_department_id')
                    ->label('Department')
                    ->relationship('department', 'title'),
                SelectFilter::make('sender')
                    ->options(CustomerMessage::SENDER_OPTIONS),
                TernaryFilter::make('is_unread')
                    ->label('Needs response'),
            ])
            ->defaultSort('created_at', 'desc')
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
                    ->successNotificationTitle('Message marked replied'),
                Action::make('markNeedsResponse')
                    ->label('Needs response')
                    ->icon(Heroicon::BellAlert)
                    ->color('warning')
                    ->visible(fn (CustomerMessage $record): bool => ! $record->is_unread)
                    ->action(fn (CustomerMessage $record) => $record->update(['is_unread' => true]))
                    ->successNotificationTitle('Message marked as needing response'),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([]);
    }
}
