<?php

namespace Applications\DarHijama\Filament\Resources\ArticleCategoryResource\Pages;

use Applications\DarHijama\Filament\Resources\ArticleCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticleCategory extends EditRecord
{
    protected static string $resource = ArticleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
