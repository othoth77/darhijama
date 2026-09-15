<?php

namespace Applications\DarHijama\Filament\Resources\ArticleResource\Pages;

use Applications\DarHijama\Filament\Resources\ArticleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('dar-hijama.articles.show', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->isPubliclyVisible()),
            DeleteAction::make(),
        ];
    }
}
