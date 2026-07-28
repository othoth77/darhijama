<?php

namespace Mythos\Core\UI\Filament;

use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Mythos\Core\Media\Contracts\MediaManager;

class MediaUploadField
{
    public static function image(
        string $name,
        string $directory,
        ?string $label = null,
    ): FileUpload {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->imageResizeMode('contain')
            ->imageResizeTargetWidth('1600')
            ->imageResizeTargetHeight('1600')
            ->imageResizeUpscale(false)
            ->disk(fn (): string => app(MediaManager::class)->defaultDisk())
            ->directory($directory)
            ->visibility((string) config('media.visibility', 'public'))
            ->saveUploadedFileUsing(
                fn (TemporaryUploadedFile $file): string => app(MediaManager::class)
                    ->upload($file, $directory, 'image')
                    ->path,
            )
            ->deleteUploadedFileUsing(
                fn (string $file): bool => app(MediaManager::class)->delete($file),
            );
    }
}
