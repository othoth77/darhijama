<?php

namespace Modules\Media\Filament\Resources;

use App\Filament\Components\SharedTableColumns;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Media\Filament\Resources\MediaResource\Pages;
use Modules\Media\Models\Media;

/**
 * Gestion des lignes de métadonnées uniquement. La suppression ne supprime
 * jamais le fichier physique en Phase 1 — voir PHASE_1.md §8.
 */
class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Médias';

    protected static ?string $navigationLabel = 'Médias';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('type')->required(),
            TextInput::make('path')->required()->disabled(),
            TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mediable_type')->label('Rattaché à'),
                TextColumn::make('type')->label('Type'),
                TextColumn::make('path')->label('Chemin')->limit(40),
                TextColumn::make('size')->label('Taille (octets)'),
                SharedTableColumns::createdAt(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
