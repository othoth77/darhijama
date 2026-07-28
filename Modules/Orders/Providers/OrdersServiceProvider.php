<?php

namespace Modules\Orders\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Orders\Models\Client;
use Modules\Orders\Models\Order;
use Modules\Orders\Observers\ClientObserver;
use Modules\Orders\Observers\OrderObserver;
use Modules\Orders\Policies\ClientPolicy;
use Modules\Orders\Policies\OrderPolicy;

/**
 * Module core (MVP) — toujours actif, aucun Feature Flag requis.
 * Suivi des commandes creees manuellement par les operateurs.
 */
class OrdersServiceProvider extends ServiceProvider
{
    protected string $name = 'Orders';

    protected string $nameLower = 'orders';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));

        Client::observe(ClientObserver::class);
        Order::observe(OrderObserver::class);

        // Enregistrement explicite (pas de dépendance à la découverte par
        // convention de Laravel) — cohérent avec le principe "vérifier, ne pas
        // supposer" appliqué au reste de la Phase 1.
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);

        $this->registerRepositoryBindings();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), $this->nameLower);
    }

    /**
     * Liaison des interfaces de Repository vers leur implémentation Eloquent
     * (Dependency Inversion — voir Repositories/Contracts).
     */
    protected function registerRepositoryBindings(): void
    {
        // Exemple :
        // $this->app->bind(
        //     \Modules\Orders\Repositories\Contracts\OrdersRepositoryInterface::class,
        //     \Modules\Orders\Repositories\EloquentOrdersRepository::class
        // );
    }
}
