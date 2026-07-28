<?php

namespace Mythos\Core\Applications\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Mythos\Core\Applications\Manifest\ManifestValidator;
use RuntimeException;

class MakeApplicationCommand extends Command
{
    protected $signature = 'mythos:make-application {name}';

    protected $description = 'Generate a minimal Mythos OS application';

    public function handle(Filesystem $files, ManifestValidator $validator): int
    {
        $name = Str::studly((string) $this->argument('name'));

        if ($name === '' || preg_match('/^[A-Z][A-Za-z0-9]*$/', $name) !== 1) {
            $this->error('Application name must contain letters and numbers and start with a letter.');

            return self::FAILURE;
        }

        $slug = Str::kebab($name);
        $root = base_path("Applications/{$name}");

        if ($files->exists($root)) {
            $this->error("Application [{$name}] already exists.");

            return self::FAILURE;
        }

        $directories = [
            'Providers',
            'Contracts',
            'Domain',
            'Application',
            'Infrastructure',
            'Http',
            'Policies',
            'routes',
            'database/migrations',
            'resources/views',
            'resources/lang',
            'config',
            'tests/Feature',
        ];

        try {
            foreach ($directories as $directory) {
                $files->ensureDirectoryExists("{$root}/{$directory}");
            }

            foreach (['Contracts', 'Domain', 'Application', 'Infrastructure', 'Http', 'Policies', 'database/migrations', 'resources/views', 'resources/lang'] as $directory) {
                $files->put("{$root}/{$directory}/.gitkeep", '');
            }

            $replacements = [
                '{{ application }}' => $name,
                '{{ namespace }}' => "Applications\\{$name}",
                '{{ json_namespace }}' => "Applications\\\\{$name}",
                '{{ slug }}' => $slug,
            ];

            $this->writeStub($files, 'Application.php.stub', "{$root}/Application.php", $replacements);
            $this->writeStub($files, 'ServiceProvider.php.stub', "{$root}/Providers/{$name}ServiceProvider.php", $replacements);
            $this->writeStub($files, 'routes.php.stub', "{$root}/routes/web.php", $replacements);
            $this->writeStub($files, 'config.php.stub', "{$root}/config/application.php", $replacements);
            $this->writeStub($files, 'HealthTest.php.stub', "{$root}/tests/Feature/ApplicationHealthTest.php", $replacements);
            $this->writeStub($files, 'mythos.json.stub', "{$root}/mythos.json", $replacements);

            $validator->validateFile("{$root}/mythos.json");
        } catch (\Throwable $exception) {
            if ($files->isDirectory($root)) {
                $files->deleteDirectory($root);
            }

            throw new RuntimeException(
                "Unable to generate application [{$name}]: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $this->components->info("Application [{$name}] generated successfully.");
        $this->line("Add base_path('Applications/{$name}/mythos.json') to config/mythos.php after review.");

        return self::SUCCESS;
    }

    private function writeStub(Filesystem $files, string $stub, string $destination, array $replacements): void
    {
        $contents = $files->get(__DIR__."/../stubs/{$stub}");
        $files->put($destination, str_replace(
            array_keys($replacements),
            array_values($replacements),
            $contents,
        ));
    }
}
