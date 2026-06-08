<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CustomerMessages\CustomerMessageResource;
use App\Models\CustomerMessage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UnreadMessagesWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Messages needing response')
            ->description('Unread customer conversations sorted for quick follow-up.')
            ->query(fn (): Builder => CustomerMessage::query()
                ->with('department')
                ->where('is_unread', true)
                ->latest()
                ->limit(6))
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('department.title')
                    ->label('Department')
                    ->wrap()
                    ->extraCellAttributes(['dir' => 'rtl']),
                TextColumn::make('text')
                    ->label('Message')
                    ->limit(80)
                    ->lineClamp(2)
                    ->wrap()
                    ->extraCellAttributes(['dir' => 'rtl']),
                TextColumn::make('time_label')
                    ->label('Time')
                    ->visibleFrom('lg'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->emptyStateHeading('No messages need a response')
            ->recordActions(ActionGroup::make([
                Action::make('openThread')
                    ->label('Open thread')
                    ->icon(Heroicon::Eye)
                    ->url(fn (CustomerMessage $record): string => CustomerMessageResource::getUrl('view', ['record' => $record])),
                Action::make('markReplied')
                    ->label('Mark replied')
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->action(fn (CustomerMessage $record) => $record->update(['is_unread' => false]))
                    ->successNotificationTitle('Message marked replied'),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->recordUrl(fn (CustomerMessage $record): string => CustomerMessageResource::getUrl('view', ['record' => $record]));
    }
}
