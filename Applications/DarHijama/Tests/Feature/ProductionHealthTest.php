<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Application\Operations\ProductionHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Mythos\Core\Identity\Models\User;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductionHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_liveness_is_minimal_and_public(): void
    {
        $this->get('/mythos/dar-hijama/health')
            ->assertOk()
            ->assertExactJson(['application' => 'dar-hijama', 'status' => 'ok']);
    }

    public function test_readiness_returns_service_unavailable_when_a_dependency_is_unhealthy(): void
    {
        $health = new class extends ProductionHealthService
        {
            public function ready(): bool
            {
                return false;
            }
        };
        $this->app->instance(ProductionHealthService::class, $health);

        $this->get('/mythos/dar-hijama/ready')
            ->assertStatus(503)
            ->assertExactJson(['status' => 'not_ready']);
    }

    public function test_diagnostics_are_authorized_and_report_each_dependency_without_secrets(): void
    {
        $this->get('/dar-hijama/operations/diagnostics')->assertRedirect();

        $user = User::factory()->create();
        foreach (app(ApplicationRegistry::class)->permissions('dar-hijama') as $definition) {
            Permission::findOrCreate($definition->name);
        }
        $user->givePermissionTo(['dar-hijama.access', 'dar-hijama.settings.manage']);

        $checks = [
            'application' => true,
            'database' => true,
            'cache' => true,
            'queue_worker' => true,
            'scheduler' => true,
            'private_storage' => true,
            'public_storage' => true,
            'environment' => true,
            'migrations' => true,
            'disk_space' => true,
        ];
        $health = new class($checks) extends ProductionHealthService
        {
            public function __construct(private readonly array $result) {}

            public function checks(bool $fresh = false): array
            {
                return $this->result;
            }
        };
        $this->app->instance(ProductionHealthService::class, $health);

        $this->actingAs($user)
            ->get('/dar-hijama/operations/diagnostics')
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('checks.database', true)
            ->assertJsonMissing(['APP_KEY'])
            ->assertJsonMissing(['password']);
    }

    public function test_each_unhealthy_dependency_makes_diagnostics_non_ready(): void
    {
        $user = User::factory()->create();
        foreach (app(ApplicationRegistry::class)->permissions('dar-hijama') as $definition) {
            Permission::findOrCreate($definition->name);
        }
        $user->givePermissionTo(['dar-hijama.access', 'dar-hijama.settings.manage']);
        $this->actingAs($user);

        $dependencies = [
            'application', 'database', 'cache', 'queue_worker', 'scheduler',
            'private_storage', 'public_storage', 'environment', 'migrations', 'disk_space',
        ];

        foreach ($dependencies as $unhealthy) {
            $checks = array_fill_keys($dependencies, true);
            $checks[$unhealthy] = false;
            $health = new class($checks) extends ProductionHealthService
            {
                public function __construct(private readonly array $result) {}

                public function checks(bool $fresh = false): array
                {
                    return $this->result;
                }
            };
            $this->app->instance(ProductionHealthService::class, $health);

            $this->get('/dar-hijama/operations/diagnostics')
                ->assertStatus(503)
                ->assertJsonPath('status', 'not_ready')
                ->assertJsonPath("checks.{$unhealthy}", false);
        }
    }
}
