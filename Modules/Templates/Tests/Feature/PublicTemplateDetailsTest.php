<?php

namespace Modules\Templates\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Templates\Models\Template;
use Tests\TestCase;

class PublicTemplateDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_template_renders_real_demo_data_media_seo_and_accessibility(): void
    {
        Storage::fake('public');
        config()->set('media.disk', 'public');

        $template = Template::factory()->create([
            'name' => 'Jardin Doré',
            'slug' => 'jardin-dore',
            'preview_image_path' => 'templates/previews/jardin.webp',
            'demo_data' => [
                'groom_name' => 'Youssef',
                'bride_name' => 'Meriem',
                'date' => '14 août 2027',
                'venue' => 'Sidi Bou Saïd',
                'message' => 'Bienvenue à notre célébration.',
                'ignored' => '<script>alert(1)</script>',
            ],
        ]);

        $response = $this->get(route('templates.show', $template->slug));

        $response->assertOk();
        $response->assertSee('Youssef');
        $response->assertSee('Meriem');
        $response->assertSee('14 août 2027');
        $response->assertSee('Sidi Bou Saïd');
        $response->assertDontSee('alert(1)', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Product"', false);
        $response->assertSee('fetchpriority="high"', false);
        $response->assertSee('alt="Aperçu du modèle Jardin Doré"', false);
        $this->assertDatabaseHas('page_views', [
            'source' => 'template',
            'subject_id' => (string) $template->id,
        ]);
    }

    public function test_inactive_and_unknown_templates_return_not_found(): void
    {
        $inactive = Template::factory()->create([
            'slug' => 'inactive-template',
            'is_active' => false,
        ]);

        $this->get(route('templates.show', $inactive->slug))->assertNotFound();
        $this->get(route('templates.show', 'unknown-template'))->assertNotFound();
    }
}
