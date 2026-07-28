<?php

namespace Mythos\Core\Applications\Registry;

use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Mythos\Core\Applications\Contracts\MythosApplication;
use Mythos\Core\Applications\Definitions\NavigationDefinition;
use Mythos\Core\Applications\Definitions\PermissionDefinition;
use Mythos\Core\Applications\Exceptions\DuplicateApplicationSlug;
use Mythos\Core\Applications\Exceptions\InvalidApplicationManifest;
use Mythos\Core\Applications\Manifest\ApplicationManifest;
use Mythos\Core\Applications\Manifest\ManifestValidator;

final class MythosApplicationRegistry implements ApplicationRegistry
{
    /** @var array<string, ApplicationManifest> */
    private array $manifests = [];

    /** @var array<string, MythosApplication> */
    private array $instances = [];

    private bool $booted = false;

    public function __construct(
        private readonly Application $app,
        private readonly ManifestValidator $validator,
    ) {}

    public function discover(array $manifestPaths): void
    {
        foreach ($manifestPaths as $manifestPath) {
            if (! is_string($manifestPath) || trim($manifestPath) === '') {
                throw new InvalidApplicationManifest('Configured application manifest paths must be non-empty strings.');
            }

            $this->registerManifest($manifestPath);
        }
    }

    public function registerManifest(string $manifestPath): ApplicationManifest
    {
        $manifest = $this->validator->validateFile($manifestPath);

        if (isset($this->manifests[$manifest->slug])) {
            throw new DuplicateApplicationSlug("Application slug [{$manifest->slug}] is already registered.");
        }

        /** @var MythosApplication $application */
        $application = $this->app->makeWith($manifest->applicationClass, [
            'applicationManifest' => $manifest,
        ]);

        $application->registering();
        $this->app->register($manifest->serviceProvider);

        foreach ($manifest->config as $config) {
            $this->app['config']->set(
                $config['key'],
                array_replace_recursive(
                    require $manifest->path($config['path']),
                    $this->app['config']->get($config['key'], []),
                ),
            );
        }

        $this->manifests[$manifest->slug] = $manifest;
        $this->instances[$manifest->slug] = $application;
        $application->registered();

        if ($this->booted) {
            $this->bootApplication($application);
        }

        return $manifest;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->instances as $application) {
            $this->bootApplication($application);
        }

        $this->booted = true;
    }

    public function applications(): array
    {
        return array_values($this->manifests);
    }

    public function find(string $slug): ?ApplicationManifest
    {
        return $this->manifests[$slug] ?? null;
    }

    public function validate(string $slug): ApplicationManifest
    {
        $manifest = $this->find($slug);

        if (! $manifest) {
            throw new InvalidApplicationManifest("Application [{$slug}] is not registered.");
        }

        return $this->validator->validateFile($manifest->manifestPath);
    }

    /** @return list<PermissionDefinition> */
    public function permissions(string $slug): array
    {
        return isset($this->instances[$slug])
            ? $this->instances[$slug]->permissions()
            : [];
    }

    /** @return list<NavigationDefinition> */
    public function navigation(string $slug): array
    {
        return isset($this->instances[$slug])
            ? $this->instances[$slug]->navigation()
            : [];
    }

    private function bootApplication(MythosApplication $application): void
    {
        $manifest = $application->manifest();
        $application->booting();

        foreach ($manifest->routes as $route) {
            Route::middleware($route['middleware'])->group($manifest->path($route['path']));
        }

        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->app['router']->getRoutes()->refreshActionLookups();

        foreach ($manifest->migrations as $migration) {
            $this->app['migrator']->path($manifest->path($migration));
        }

        if ($manifest->views !== null) {
            View::addNamespace($manifest->viewNamespace, $manifest->path($manifest->views));
        }

        if ($manifest->translations !== null) {
            $this->app['translator']->addNamespace(
                $manifest->viewNamespace,
                $manifest->path($manifest->translations),
            );
        }

        foreach ($manifest->listeners as $listener) {
            Event::listen($listener['event'], $listener['listener']);
        }

        if ($this->app->runningInConsole() && $manifest->commands !== []) {
            Artisan::starting(
                function (Artisan $artisan) use ($manifest): void {
                    $artisan->resolveCommands($manifest->commands);
                },
            );
        }

        $application->booted();
    }
}
