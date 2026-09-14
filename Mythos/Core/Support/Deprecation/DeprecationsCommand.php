<?php

namespace Mythos\Core\Support\Deprecation;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class DeprecationsCommand extends Command
{
    protected $signature = 'mythos:deprecations {--write : Write docs/DEPRECATIONS.md}';

    protected $description = 'List documented Mythos OS API deprecations';

    public function handle(DeprecationRegistry $registry, Filesystem $files): int
    {
        $markdown = $registry->markdown();

        if ($this->option('write')) {
            $files->put(base_path('docs/DEPRECATIONS.md'), $markdown);
            $this->components->info('Deprecation documentation generated.');

            return self::SUCCESS;
        }

        $this->output->write($markdown);

        return self::SUCCESS;
    }
}
