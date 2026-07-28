<?php

namespace Mythos\Core\Applications\Manifest;

final readonly class ApplicationManifest
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $namespace,
        public string $version,
        public string $description,
        public string $applicationClass,
        public string $serviceProvider,
        public array $capabilities,
        public array $permissions,
        public array $routes,
        public array $migrations,
        public array $config,
        public string $viewNamespace,
        public ?string $views,
        public ?string $translations,
        public array $commands,
        public array $listeners,
        public array $assets,
        public string $coreCompatibility,
        public string $manifestPath,
    ) {}

    public function rootPath(): string
    {
        return dirname($this->manifestPath);
    }

    public function path(string $relativePath): string
    {
        return $this->rootPath().DIRECTORY_SEPARATOR.str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $relativePath,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'namespace' => $this->namespace,
            'version' => $this->version,
            'description' => $this->description,
            'application_class' => $this->applicationClass,
            'service_provider' => $this->serviceProvider,
            'core_capabilities' => $this->capabilities,
            'permissions' => $this->permissions,
            'routes' => $this->routes,
            'migrations' => $this->migrations,
            'config' => $this->config,
            'view_namespace' => $this->viewNamespace,
            'views' => $this->views,
            'translations' => $this->translations,
            'commands' => $this->commands,
            'event_listeners' => $this->listeners,
            'assets' => $this->assets,
            'compatibility' => ['mythos_core' => $this->coreCompatibility],
        ];
    }
}
