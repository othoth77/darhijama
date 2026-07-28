<?php

namespace Modules\Media\Filament\Resources\MediaResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Media\Filament\Resources\MediaResource;
use Mythos\Core\Media\Contracts\MediaManager as MediaService;
use Mythos\Core\Media\Models\Media;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->using(
                    fn (Media $record): bool => app(MediaService::class)
                        ->deleteMedia($record, deleteFile: false)
                ),
        ];
    }
}
