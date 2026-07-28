<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SharedBladeComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_cta_preserves_tracking_and_responsive_classes(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-shared.whatsapp-cta
                href="https://wa.me/21600000000"
                source="test_cta"
                :context="['invitation_id' => 12]"
                class="w-full sm:w-auto"
            >
                Contact
            </x-shared.whatsapp-cta>
        BLADE);

        $this->assertStringContainsString('https://wa.me/21600000000', $html);
        $this->assertStringContainsString('whatsappCta', $html);
        $this->assertStringContainsString('test_cta', $html);
        $this->assertStringContainsString('invitation_id', $html);
        $this->assertStringContainsString('w-full sm:w-auto', $html);
    }

    public function test_seo_and_open_graph_metadata_render_all_critical_tags(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-shared.seo-metadata
                title="Shared title"
                description="Shared description"
                canonical="https://example.test/public"
                image="https://example.test/image.jpg"
            />
        BLADE);

        $this->assertStringContainsString('<title>Shared title</title>', $html);
        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('property="og:image"', $html);
        $this->assertStringContainsString('summary_large_image', $html);
    }

    public function test_feedback_empty_loading_and_media_components_are_accessible(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-shared.confirmation-message message="Saved" />
            <x-shared.empty-state title="Nothing here" description="Try later" />
            <x-shared.loading-state label="Please wait" />
            <x-shared.responsive-media
                src="/photo.jpg"
                alt="Responsive photo"
                width="640"
                height="480"
                class="h-auto w-full"
            />
        BLADE);

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('Nothing here', $html);
        $this->assertStringContainsString('Please wait', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('width="640"', $html);
        $this->assertStringContainsString('h-auto w-full', $html);
    }

    public function test_public_pages_keep_responsive_breakpoints_after_refactor(): void
    {
        $landing = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('sm:inline-flex', $landing);
        $this->assertStringContainsString('lg:grid-cols-2', $landing);
        $this->assertStringContainsString('md:hidden', $landing);
    }
}
