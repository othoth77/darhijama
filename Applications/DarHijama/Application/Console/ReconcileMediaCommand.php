<?php

namespace Applications\DarHijama\Application\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Mythos\Core\Media\Models\Media;

class ReconcileMediaCommand extends Command
{
    protected $signature = 'dar-hijama:media-reconcile {--disk=local}';

    protected $description = 'Report missing and orphaned media without deleting files';

    public function handle(): int
    {
        $disk = (string) $this->option('disk');
        $databasePaths = Media::query()
            ->where('disk', $disk)
            ->pluck('path')
            ->filter()
            ->values();
        $files = collect(Storage::disk($disk)->allFiles());
        $missing = $databasePaths->diff($files)->values();
        $orphans = $files->diff($databasePaths)->reject(
            fn (string $path) => str_starts_with($path, '.health/'),
        )->values();

        $this->line(json_encode([
            'disk' => $disk,
            'mode' => 'dry-run',
            'database_files' => $databasePaths->count(),
            'storage_files' => $files->count(),
            'missing' => $missing->all(),
            'orphans' => $orphans->all(),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
