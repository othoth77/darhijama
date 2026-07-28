<?php

namespace Modules\Templates\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Templates\Filament\Resources\TemplateCategoryResource\Pages;
use Modules\Templates\Models\TemplateCategory;

/**
 * Simple Resource (une seule page ManageRecords, pas de List/Create/Edit
 * séparées) — décision produit initiale pour cette table de référence.
 */
class TemplateCategoryResource extends Resource
{
    protected static ?string $model = TemplateCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Modèles';

    protected static ?string $navigationLabel = 'Catégories';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nom')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true),
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
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('order')->label('Ordre')->sortable(),
                TextColumn::make('templates_count')->counts('templates')->label('Modèles'),
            ])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTemplateCategories::route('/'),
        ];
    }
}
