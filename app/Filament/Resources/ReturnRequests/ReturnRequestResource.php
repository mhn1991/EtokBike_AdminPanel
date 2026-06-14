<?php

namespace App\Filament\Resources\ReturnRequests;

use App\Filament\Resources\Receipts\ReceiptResource;
use App\Filament\Resources\ReturnRequests\Pages\CreateReturnRequest;
use App\Filament\Resources\ReturnRequests\Pages\EditReturnRequest;
use App\Filament\Resources\ReturnRequests\Pages\ListReturnRequests;
use App\Filament\Resources\ReturnRequests\RelationManagers\ItemsRelationManager;
use App\Models\ReturnRequest;
use App\Support\Receipts\ReceiptGenerator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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

class ReturnRequestResource extends Resource
{
    protected static ?string $model = ReturnRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Returns';

    protected static string|\UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'return_number';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Return')
                    ->description('Customer return and refund workflow.')
                    ->columns(3)
                    ->schema([
                        TextInput::make('return_number')
                            ->helperText('Leave blank to auto-generate.')
                            ->maxLength(255),
                        Select::make('order_id')
                            ->label('Order')
                            ->relationship('order', 'order_number')
                            ->native(false)
                            ->searchable()
                            ->preload(),
                        TextInput::make('reason')
                            ->maxLength(255),
                        ToggleButtons::make('status')
                            ->options(ReturnRequest::STATUS_OPTIONS)
                            ->colors([
                                'requested' => 'warning',
                                'approved' => 'info',
                                'received' => 'primary',
                                'refunded' => 'success',
                                'rejected' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('requested')
                            ->columnSpan(2),
                        ToggleButtons::make('refund_status')
                            ->options(ReturnRequest::REFUND_STATUS_OPTIONS)
                            ->colors([
                                'none' => 'gray',
                                'pending' => 'warning',
                                'paid' => 'success',
                                'failed' => 'danger',
                            ])
                            ->inline()
                            ->required()
                            ->default('none'),
                        DateTimePicker::make('requested_at')
                            ->seconds(false)
                            ->default(now()),
                        DateTimePicker::make('received_at')
                            ->seconds(false),
                        TextInput::make('refund_total')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->integer()
                            ->suffix('IRR')
                            ->default(0),
                    ]),
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('customer_phone')
                            ->tel()
                            ->maxLength(255),
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('return_number')
                    ->label('Return')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->description(fn (ReturnRequest $record): string => collect([$record->customer_phone, $record->order?->order_number])->filter()->join(' · '))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ReturnRequest::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'info',
                        'received' => 'primary',
                        'refunded' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('refund_status')
                    ->label('Refund')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ReturnRequest::REFUND_STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->visibleFrom('md')
                    ->sortable(),
                TextColumn::make('refund_total')
                    ->formatStateUsing(fn (?int $state): string => number_format($state ?? 0))
                    ->visibleFrom('lg')
                    ->sortable(),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->placeholder('-')
                    ->visibleFrom('xl')
                    ->sortable(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentCheck)
            ->emptyStateHeading('No returns yet')
            ->emptyStateDescription('Create return requests for refunds, inspections, and restocking decisions.')
            ->filters([
                SelectFilter::make('status')
                    ->options(ReturnRequest::STATUS_OPTIONS),
                SelectFilter::make('refund_status')
                    ->options(ReturnRequest::REFUND_STATUS_OPTIONS),
            ])
            ->defaultSort('requested_at', 'desc')
            ->recordActions(ActionGroup::make([
                EditAction::make(),
                Action::make('generateCreditNote')
                    ->label('Generate credit note')
                    ->icon(Heroicon::ReceiptRefund)
                    ->color('danger')
                    ->action(function (ReturnRequest $record) {
                        $receipt = app(ReceiptGenerator::class)->creditNoteForReturn($record);

                        return redirect(ReceiptResource::getUrl('view', ['record' => $receipt]));
                    }),
            ])
                ->label('Actions')
                ->icon(Heroicon::EllipsisHorizontal)
                ->iconButton()
                ->color('gray'))
            ->recordActionsColumnLabel('')
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReturnRequests::route('/'),
            'create' => CreateReturnRequest::route('/create'),
            'edit' => EditReturnRequest::route('/{record}/edit'),
        ];
    }
}
