<?php

namespace Mythos\Core\UI\Filament;

use Filament\Tables\Columns\TextColumn;

class SharedTableColumns
{
    public static function createdAt(string $label = 'Créé le'): TextColumn
    {
        return TextColumn::make('created_at')
            ->label($label)
            ->dateTime()
            ->sortable();
    }

    public static function publishedAt(): TextColumn
    {
        return TextColumn::make('published_at')
            ->label('Publiée le')
            ->dateTime();
    }
}
