<?php

namespace Mythos\Core\Media\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Templates\Models\Template;
use Tests\TestCase;

class MediaMorphRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_stores_the_morph_map_alias_not_the_raw_class(): void
    {
        $template = Template::factory()->create();

        $media = $template->media()->create([
            'disk' => 'public',
            'path' => 'templates/test.jpg',
            'type' => 'image',
        ]);

        $this->assertSame('template', $media->getRawOriginal('mediable_type'));
        $this->assertNotSame(Template::class, $media->getRawOriginal('mediable_type'));
    }

    public function test_deleting_a_template_deletes_only_the_media_row_not_the_physical_file(): void
    {
        Storage::fake('public');

        $template = Template::factory()->create();
        $template->media()->create([
            'disk' => 'public',
            'path' => 'templates/test.jpg',
            'type' => 'image',
        ]);
        Storage::disk('public')->put('templates/test.jpg', 'fake-content');

        $template->delete();

        $this->assertDatabaseCount('media', 0);
        // Le fichier physique n'est jamais touché par l'Observer en Phase 1 (PHASE_1.md §8).
        Storage::disk('public')->assertExists('templates/test.jpg');
    }
}
