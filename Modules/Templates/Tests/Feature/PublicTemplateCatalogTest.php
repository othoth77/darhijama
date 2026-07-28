<?php

namespace Modules\Templates\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Templates\Models\Template;
use Modules\Templates\Models\TemplateCategory;
use Tests\TestCase;

class PublicTemplateCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_lists_only_active_templates_with_media_urls_and_seo(): void
    {
        Storage::fake('public');
        config()->set('media.disk', 'public');

        $active = Template::factory()->create([
            'name' => 'Élégance Active',
            'slug' => 'elegance-active',
            'preview_image_path' => 'templates/previews/active.webp',
            'is_active' => true,
        ]);
        Template::factory()->create([
            'name' => 'Modèle Inactif',
            'is_active' => false,
        ]);

        $response = $this->get(route('templates.index'));

        $response->assertOk();
        $response->assertSee($active->name);
        $response->assertDontSee('Modèle Inactif');
        $response->assertSee(Storage::disk('public')->url($active->preview_image_path), false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('loading="lazy"', false);
        $response->assertSee('width="640"', false);
        $this->assertDatabaseHas('page_views', ['source' => 'templates']);
    }

    public function test_catalog_supports_search_category_sorting_and_pagination(): void
    {
        $modern = TemplateCategory::factory()->create(['name' => 'Moderne', 'slug' => 'moderne']);
        $classic = TemplateCategory::factory()->create(['name' => 'Classique', 'slug' => 'classique']);

        Template::factory()->for($modern, 'category')->create([
            'name' => 'Zénith Moderne',
            'description' => 'Une création géométrique',
        ]);
        Template::factory()->for($classic, 'category')->create([
            'name' => 'Alpha Classique',
            'description' => 'Une création traditionnelle',
        ]);
        Template::factory()->count(11)->for($modern, 'category')->create();

        $this->get(route('templates.index', [
            'search' => 'géométrique',
            'category' => 'moderne',
            'sort' => 'name',
        ]))
            ->assertOk()
            ->assertSee('Zénith Moderne')
            ->assertDontSee('Alpha Classique');

        $this->get(route('templates.index'))
            ->assertOk()
            ->assertSee('page=2', false);
    }

    public function test_catalog_validation_rejects_unknown_sorting(): void
    {
        $this->get(route('templates.index', ['sort' => 'unsafe']))
            ->assertSessionHasErrors('sort');
    }
}
