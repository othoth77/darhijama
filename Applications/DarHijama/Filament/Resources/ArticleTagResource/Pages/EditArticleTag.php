<?php

namespace Applications\DarHijama\Filament\Resources\ArticleTagResource\Pages;

use Applications\DarHijama\Filament\Resources\ArticleTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticleTag extends EditRecord
{
    protected static string $resource = ArticleTagResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
