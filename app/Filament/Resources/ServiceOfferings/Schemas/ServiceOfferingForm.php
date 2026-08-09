<?php

namespace App\Filament\Resources\ServiceOfferings\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceOfferingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Service offering'))
                    ->description(__('The service card customers see in the mobile app.'))
                    ->columns(3)
                    ->schema([
                        Select::make('service_category_id')
                            ->label('Category')
                            ->relationship('category', 'label')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->helperText(__('Stable service ID used by the mobile app.'))
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->maxLength(255),
                        TextInput::make('subtitle')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('price_label')
                            ->maxLength(255),
                        RichEditor::make('description')
                            ->helperText(__('Shown on the service card in the app. The mobile app receives a plain-text version automatically; keep it short and avoid tables or columns.'))
                            ->toolbarButtons([
                                ['paragraph', 'h1', 'h2', 'h3', 'h4', 'lead', 'small'],
                                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript'],
                                ['textColor', 'highlight', 'code', 'link'],
                                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                                ['bulletList', 'orderedList', 'blockquote', 'codeBlock', 'horizontalRule', 'details', 'grid', 'table'],
                                ['attachFiles', 'clearFormatting', 'undo', 'redo'],
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('mobile/service-descriptions')
                            ->fileAttachmentsVisibility('public')
                            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->fileAttachmentsMaxSize(4096)
                            ->preventFileAttachmentPathTampering()
                            ->resizableImages()
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Visible in app')
                            ->required()
                            ->default(true),
                    ]),
                Section::make(__('App card'))
                    ->description(__('Thumbnail treatment for service lists.'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('thumbnail_text')
                            ->required()
                            ->maxLength(255)
                            ->default('ETOK'),
                        ColorPicker::make('thumbnail_color')
                            ->required()
                            ->hex()
                            ->default('#101114'),
                        FileUpload::make('image_url')
                            ->label('Service image')
                            ->disk('public')
                            ->directory('mobile/services')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('160')
                            ->openable()
                            ->downloadable()
                            ->maxSize(4096),
                    ]),
            ]);
    }
}
