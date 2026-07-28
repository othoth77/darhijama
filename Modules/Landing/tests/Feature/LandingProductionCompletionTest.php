<?php

namespace Modules\Landing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Templates\Models\Template;
use Tests\TestCase;

class LandingProductionCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_uses_active_dynamic_templates_and_complete_metadata(): void
    {
        $visible = Template::factory()->create([
            'name' => 'Dynamic Visible Template',
            'slug' => 'dynamic-visible',
            'is_active' => true,
            'order' => 1,
        ]);
        Template::factory()->create([
            'name' => 'Hidden Template',
            'is_active' => false,
        ]);

        $response = $this->get(route('landing.index'));

        $response->assertOk();
        $response->assertSee($visible->name);
        $response->assertSee(route('templates.show', $visible->slug), false);
        $response->assertDontSee('Hidden Template');
        $response->assertDontSee('Ivoire Élégance');
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Service"', false);
        $response->assertSee('lg:grid-cols-4', false);
    }

    public function test_sitemap_and_robots_include_only_public_production_urls(): void
    {
        $active = Template::factory()->create(['slug' => 'public-template', 'is_active' => true]);
        $inactive = Template::factory()->create(['slug' => 'private-template', 'is_active' => false]);

        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: '.route('sitemap'))
            ->assertSee('Disallow: /admin');

        $sitemap = $this->get(route('sitemap'));
        $sitemap->assertOk();
        $sitemap->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $sitemap->assertSee(route('templates.show', $active->slug), false);
        $sitemap->assertDontSee(route('templates.show', $inactive->slug), false);
        $sitemap->assertSee(route('legal.mentions'), false);
        $sitemap->assertSee(route('legal.privacy'), false);
    }

    public function test_legal_pages_are_integrated_and_accessible_from_the_footer(): void
    {
        $landing = $this->get(route('landing.index'));

        $landing->assertSee(route('legal.mentions'), false);
        $landing->assertSee(route('legal.privacy'), false);

        $this->get(route('legal.mentions'))
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('rel="canonical"', false);
        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('<h1', false)
            ->assertSee('Données collectées');
    }
}
