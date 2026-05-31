<?php

namespace App\Filament\Resources\CustomerMessages\Tables;

use App\Models\CustomerMessage;
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
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('sender')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => CustomerMessage::SENDER_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'client' ? 'warning' : 'info')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('text')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('time_label')
                    ->searchable(),
                IconColumn::make('is_unread')
                    ->label('Needs response')
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
