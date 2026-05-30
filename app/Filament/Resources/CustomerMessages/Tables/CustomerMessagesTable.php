<?php

namespace App\Filament\Resources\CustomerMessages\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('department.title')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('sender')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('time_label')
                    ->searchable(),
                IconColumn::make('is_unread')
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
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
