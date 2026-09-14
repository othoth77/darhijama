<?php

namespace Mythos\Core\Applications\Console;

use Illuminate\Console\Command;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;

class InspectApplicationCommand extends Command
{
    protected $signature = 'mythos:application:inspect {slug}';

    protected $description = 'Inspect a registered Mythos OS application';

    public function handle(ApplicationRegistry $registry): int
    {
        $manifest = $registry->find((string) $this->argument('slug'));

        if (! $manifest) {
            $this->error('Application is not registered.');

            return self::FAILURE;
        }

        $this->table(['Property', 'Value'], [
            ['Name', $manifest->name],
            ['Slug', $manifest->slug],
            ['Version', $manifest->version],
            ['Provider', $manifest->serviceProvider],
            ['Manifest', $manifest->manifestPath],
            ['Core compatibility', $manifest->coreCompatibility],
            ['Capabilities', implode(', ', $manifest->capabilities)],
            ['Routes', (string) count($manifest->routes)],
            ['Migrations', (string) count($manifest->migrations)],
            ['Permissions', implode(', ', $manifest->permissions)],
        ]);

        return self::SUCCESS;
    }
}
