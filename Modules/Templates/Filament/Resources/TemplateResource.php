<?php

namespace Modules\Templates\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Templates\Filament\Resources\TemplateResource\Pages;
use Modules\Templates\Models\Template;
use Modules\Templates\Models\TemplateCategory;
use Mythos\Core\Media\Contracts\MediaManager as MediaService;
use Mythos\Core\UI\Filament\MediaUploadField;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Modèles';

    protected static ?string $navigationLabel = 'Modèles';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('template_category_id')
                ->label('Catégorie')
                ->relationship('category', 'name')
                ->options(TemplateCategory::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),
            TextInput::make('name')
                ->label('Nom')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),
            Textarea::make('description')
                ->columnSpanFull(),
            MediaUploadField::image('preview_image_path', 'templates/previews', 'Image de prévisualisation'),

            Toggle::make('is_active')
                ->label('Actif')
                ->default(true),
            TextInput::make('order')
                ->label('Ordre')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview_image_path')
                    ->label('Aperçu')
                    ->disk(fn (): string => app(MediaService::class)->defaultDisk()),
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('category.name')->label('Catégorie')->sortable(),
                IconColumn::make('is_active')->label('Actif')->boolean(),
                TextColumn::make('order')->label('Ordre')->sortable(),
            ])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
