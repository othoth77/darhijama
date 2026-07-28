<?php

namespace Mythos\Core\Applications\Console;

use Illuminate\Console\Command;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Throwable;

class ValidateApplicationCommand extends Command
{
    protected $signature = 'mythos:application:validate {slug}';

    protected $description = 'Validate a registered Mythos OS application manifest';

    public function handle(ApplicationRegistry $registry): int
    {
        try {
            $manifest = $registry->validate((string) $this->argument('slug'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info(
            "Application [{$manifest->slug}] is valid and compatible with Mythos Core.",
        );

        return self::SUCCESS;
    }
}
