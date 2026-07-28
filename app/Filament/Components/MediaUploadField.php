<?php

namespace App\Filament\Components;

use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Modules\Media\Services\MediaService;

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
            ->disk(fn (): string => app(MediaService::class)->defaultDisk())
            ->directory($directory)
            ->visibility((string) config('media.visibility', 'public'))
            ->saveUploadedFileUsing(
                fn (TemporaryUploadedFile $file): string => app(MediaService::class)
                    ->upload($file, $directory, 'image')
                    ->path,
            )
            ->deleteUploadedFileUsing(
                fn (string $file): bool => app(MediaService::class)->delete($file),
            );
    }
}
