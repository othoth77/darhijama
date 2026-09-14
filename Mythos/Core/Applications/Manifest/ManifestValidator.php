<?php

namespace Mythos\Core\Applications\Manifest;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use JsonException;
use Mythos\Core\Applications\Capabilities\CoreCapabilityCatalog;
use Mythos\Core\Applications\Contracts\MythosApplication;
use Mythos\Core\Applications\Exceptions\IncompatibleApplication;
use Mythos\Core\Applications\Exceptions\InvalidApplicationManifest;

final class ManifestValidator
{
    public const CORE_VERSION = '1.0.0';

    private const REQUIRED_KEYS = [
        'name',
        'slug',
        'namespace',
        'version',
        'description',
        'application_class',
        'service_provider',
        'core_capabilities',
        'permissions',
        'routes',
        'migrations',
        'config',
        'view_namespace',
        'views',
        'translations',
        'commands',
        'event_listeners',
        'assets',
        'compatibility',
    ];

    public function __construct(
        private readonly CoreCapabilityCatalog $capabilities,
    ) {}

    public function validateFile(string $manifestPath): ApplicationManifest
    {
        if (! is_file($manifestPath) || ! is_readable($manifestPath)) {
            throw new InvalidApplicationManifest("Application manifest is not readable: {$manifestPath}");
        }

        try {
            $data = json_decode(
                file_get_contents($manifestPath) ?: '',
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new InvalidApplicationManifest(
                "Application manifest contains invalid JSON: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        if (! is_array($data)) {
            throw new InvalidApplicationManifest('Application manifest must contain a JSON object.');
        }

        $this->validateShape($data);

        $manifest = new ApplicationManifest(
            name: $data['name'],
            slug: $data['slug'],
            namespace: $data['namespace'],
            version: $data['version'],
            description: $data['description'],
            applicationClass: $data['application_class'],
            serviceProvider: $data['service_provider'],
            capabilities: $data['core_capabilities'],
            permissions: $data['permissions'],
            routes: $data['routes'],
            migrations: $data['migrations'],
            config: $data['config'],
            viewNamespace: $data['view_namespace'],
            views: $data['views'],
            translations: $data['translations'],
            commands: $data['commands'],
            listeners: $data['event_listeners'],
            assets: $data['assets'],
            coreCompatibility: $data['compatibility']['mythos_core'],
            manifestPath: realpath($manifestPath) ?: $manifestPath,
        );

        $this->validateClasses($manifest);
        $this->validatePaths($manifest);
        $this->validateCompatibility($manifest);

        return $manifest;
    }

    private function validateShape(array $data): void
    {
        $missing = array_diff(self::REQUIRED_KEYS, array_keys($data));
        $unknown = array_diff(array_keys($data), self::REQUIRED_KEYS);

        if ($missing !== []) {
            throw new InvalidApplicationManifest('Missing manifest keys: '.implode(', ', $missing));
        }

        if ($unknown !== []) {
            throw new InvalidApplicationManifest('Unknown manifest keys: '.implode(', ', $unknown));
        }

        foreach (['name', 'slug', 'namespace', 'version', 'description', 'application_class', 'service_provider', 'view_namespace'] as $key) {
            if (! is_string($data[$key]) || trim($data[$key]) === '') {
                throw new InvalidApplicationManifest("Manifest key [{$key}] must be a non-empty string.");
            }
        }

        if (preg_match('/^[a-z][a-z0-9-]*$/', $data['slug']) !== 1) {
            throw new InvalidApplicationManifest('Application slug must use lowercase letters, numbers, and hyphens.');
        }

        if (preg_match('/^[A-Z][A-Za-z0-9]*(\\\\[A-Z][A-Za-z0-9]*)*\\\\?$/', $data['namespace']) !== 1) {
            throw new InvalidApplicationManifest('Application namespace is invalid.');
        }

        foreach (['core_capabilities', 'permissions', 'routes', 'migrations', 'config', 'commands', 'event_listeners', 'assets'] as $key) {
            if (! is_array($data[$key]) || ! array_is_list($data[$key])) {
                throw new InvalidApplicationManifest("Manifest key [{$key}] must be a JSON list.");
            }
        }

        foreach ($data['core_capabilities'] as $capability) {
            if (! is_string($capability) || ! $this->capabilities->supports($capability)) {
                throw new InvalidApplicationManifest("Unknown Core capability: {$capability}");
            }
        }

        if (count($data['core_capabilities']) !== count(array_unique($data['core_capabilities']))) {
            throw new InvalidApplicationManifest('Core capabilities must not contain duplicates.');
        }

        foreach ($data['permissions'] as $permission) {
            if (! is_string($permission) || preg_match('/^[a-z][a-z0-9.-]+$/', $permission) !== 1) {
                throw new InvalidApplicationManifest('Every permission must be a namespaced permission string.');
            }
        }

        if (count($data['permissions']) !== count(array_unique($data['permissions']))) {
            throw new InvalidApplicationManifest('Permissions must not contain duplicates.');
        }

        foreach ($data['routes'] as $route) {
            if (! is_array($route) || array_diff(array_keys($route), ['path', 'middleware']) !== [] || ! isset($route['path'], $route['middleware']) || ! is_string($route['path']) || ! is_array($route['middleware'])) {
                throw new InvalidApplicationManifest('Every route entry requires a path and middleware list.');
            }

            if (array_filter($route['middleware'], fn ($middleware) => ! is_string($middleware)) !== []) {
                throw new InvalidApplicationManifest('Route middleware values must be strings.');
            }
        }

        foreach (['migrations', 'commands', 'assets'] as $key) {
            if (array_filter($data[$key], fn ($value) => ! is_string($value)) !== []) {
                throw new InvalidApplicationManifest("Manifest key [{$key}] accepts string values only.");
            }
        }

        foreach ($data['event_listeners'] as $listener) {
            if (! is_array($listener) || array_keys($listener) !== ['event', 'listener'] || ! is_string($listener['event']) || ! is_string($listener['listener'])) {
                throw new InvalidApplicationManifest('Event listeners require event and listener class names.');
            }
        }

        if (! is_array($data['compatibility']) || array_keys($data['compatibility']) !== ['mythos_core']) {
            throw new InvalidApplicationManifest('Compatibility must contain only the mythos_core constraint.');
        }

        if (! is_string($data['compatibility']['mythos_core'])) {
            throw new InvalidApplicationManifest('Mythos Core compatibility must be a string.');
        }

        foreach (['views', 'translations'] as $key) {
            if ($data[$key] !== null && ! is_string($data[$key])) {
                throw new InvalidApplicationManifest("Manifest key [{$key}] must be a path or null.");
            }
        }
    }

    private function validateClasses(ApplicationManifest $manifest): void
    {
        if (! class_exists($manifest->applicationClass) || ! is_subclass_of($manifest->applicationClass, MythosApplication::class)) {
            throw new InvalidApplicationManifest(
                "Application class [{$manifest->applicationClass}] must implement ".MythosApplication::class.'.',
            );
        }

        if (! class_exists($manifest->serviceProvider) || ! is_subclass_of($manifest->serviceProvider, ServiceProvider::class)) {
            throw new InvalidApplicationManifest(
                "Service provider [{$manifest->serviceProvider}] must extend ".ServiceProvider::class.'.',
            );
        }

        foreach ($manifest->commands as $command) {
            if (! class_exists($command) || ! is_subclass_of($command, Command::class)) {
                throw new InvalidApplicationManifest("Application command [{$command}] must extend ".Command::class.'.');
            }
        }

        foreach ($manifest->listeners as $listener) {
            if (! class_exists($listener['event']) || ! class_exists($listener['listener'])) {
                throw new InvalidApplicationManifest('Application event and listener classes must be autoloadable.');
            }
        }
    }

    private function validatePaths(ApplicationManifest $manifest): void
    {
        foreach ($manifest->routes as $route) {
            $this->assertRelativeExistingPath($manifest, $route['path'], 'route');
        }

        foreach ($manifest->migrations as $path) {
            $this->assertRelativeExistingPath($manifest, $path, 'migration');
        }

        foreach ($manifest->config as $entry) {
            if (! is_array($entry) || ! isset($entry['key'], $entry['path']) || ! is_string($entry['key']) || ! is_string($entry['path'])) {
                throw new InvalidApplicationManifest('Every config entry requires string key and path values.');
            }

            $this->assertRelativeExistingPath($manifest, $entry['path'], 'config');
        }

        foreach (array_filter([$manifest->views, $manifest->translations]) as $path) {
            $this->assertRelativeExistingPath($manifest, $path, 'resource');
        }

        foreach ($manifest->assets as $path) {
            $this->assertRelativeExistingPath($manifest, $path, 'asset');
        }
    }

    private function assertRelativeExistingPath(ApplicationManifest $manifest, string $path, string $type): void
    {
        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_contains($path, '..')) {
            throw new InvalidApplicationManifest("Application {$type} path must be relative and cannot traverse directories: {$path}");
        }

        if (! file_exists($manifest->path($path))) {
            throw new InvalidApplicationManifest("Application {$type} path does not exist: {$path}");
        }
    }

    private function validateCompatibility(ApplicationManifest $manifest): void
    {
        $constraint = trim($manifest->coreCompatibility);

        if (preg_match('/^\^(\d+)\.(\d+)$/', $constraint, $matches) !== 1) {
            throw new InvalidApplicationManifest('Mythos Core compatibility must use the ^major.minor format.');
        }

        [$major, $minor] = array_map('intval', array_slice($matches, 1));
        [$coreMajor, $coreMinor] = array_map('intval', explode('.', self::CORE_VERSION));

        if ($major !== $coreMajor || $coreMinor < $minor) {
            throw new IncompatibleApplication(
                "Application [{$manifest->slug}] requires Mythos Core {$constraint}; installed version is ".self::CORE_VERSION.'.',
            );
        }
    }
}
