<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Modules\Invitations\Filament\Resources\InvitationResource;
use Modules\Media\Filament\Resources\MediaResource;
use Modules\Orders\Filament\Resources\ClientResource;
use Modules\Orders\Filament\Resources\OrderResource;
use Modules\Templates\Filament\Resources\TemplateCategoryResource;
use Modules\Templates\Filament\Resources\TemplateResource;
use Tests\TestCase;

/**
 * Vérifie que la stratégie de découverte par motif générique
 * (AdminPanelProvider::panel(), PHASE_1.md §3) enregistre bien les Filament
 * Resources de chaque module actif, sans dépendre d'un appel depuis les
 * ServiceProvider de modules.
 */
class FilamentResourcesRegisteredTest extends TestCase
{
    public function test_all_module_resources_are_registered_on_the_admin_panel(): void
    {
        $resources = Filament::getPanel('admin')->getResources();

        foreach ([
            ClientResource::class,
            OrderResource::class,
            TemplateCategoryResource::class,
            TemplateResource::class,
            InvitationResource::class,
            MediaResource::class,
        ] as $resourceClass) {
            $this->assertContains(
                $resourceClass,
                $resources,
                "{$resourceClass} n'est pas enregistrée sur le panel admin."
            );
        }
    }

    public function test_expected_resource_routes_exist(): void
    {
        $this->assertTrue(Route::has('filament.admin.resources.clients.index'));
        $this->assertTrue(Route::has('filament.admin.resources.orders.index'));
        $this->assertTrue(Route::has('filament.admin.resources.templates.index'));
        $this->assertTrue(Route::has('filament.admin.resources.invitations.index'));
    }
}
