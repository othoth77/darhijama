<?php

namespace Mythos\Core\Applications\Console;

use Illuminate\Console\Command;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;

class ListApplicationsCommand extends Command
{
    protected $signature = 'mythos:applications';

    protected $description = 'List registered Mythos OS applications';

    public function handle(ApplicationRegistry $registry): int
    {
        $this->table(
            ['Name', 'Slug', 'Version', 'Provider', 'Status'],
            collect($registry->applications())->map(fn ($manifest) => [
                $manifest->name,
                $manifest->slug,
                $manifest->version,
                $manifest->serviceProvider,
                'enabled',
            ])->all(),
        );

        return self::SUCCESS;
    }
}
