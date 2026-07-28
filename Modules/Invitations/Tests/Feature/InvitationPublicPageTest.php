<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Models\ProgramStep;
use Modules\Media\Models\Media;
use Tests\TestCase;

/**
 * Phase 3 — page publique https://notrejour.tn/i/{public_token}. Seules les
 * invitations publiées (InvitationStatus::Publie) doivent être visibles ;
 * toute autre valeur ou tout token inconnu doit renvoyer un 404 générique
 * (aucune fuite d'existence d'une invitation non publiée).
 */
class InvitationPublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_a_published_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
            'groom_name' => 'Karim',
            'bride_name' => 'Leila',
        ]);

        $response = $this->get("/i/{$invitation->public_token}");

        $response->assertOk();
        $response->assertSee('Karim');
        $response->assertSee('Leila');
    }

    public function test_it_returns_404_for_a_draft_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ]);

        $response = $this->get("/i/{$invitation->public_token}");

        $response->assertNotFound();
    }

    public function test_it_returns_404_for_an_archived_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Archive,
            'published_at' => now(),
        ]);

        $response = $this->get("/i/{$invitation->public_token}");

        $response->assertNotFound();
    }

    public function test_it_returns_404_for_an_unknown_token(): void
    {
        $response = $this->get('/i/UNKNOWN0000000000000TOKEN');

        $response->assertNotFound();
    }

    public function test_it_displays_the_program_steps(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        ProgramStep::factory()->for($invitation)->create([
            'title' => 'Cérémonie religieuse',
            'order' => 1,
        ]);

        $response = $this->get("/i/{$invitation->public_token}");

        $response->assertOk();
        $response->assertSee('Cérémonie religieuse');
    }

    public function test_it_displays_the_gallery_images(): void
    {
        Storage::fake('public');

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        Media::factory()->create([
            'mediable_type' => 'invitation',
            'mediable_id' => $invitation->id,
            'disk' => 'public',
            'path' => 'invitations/photo-1.jpg',
            'type' => 'image',
            'order' => 1,
        ]);
        Storage::disk('public')->put('invitations/photo-1.jpg', 'image');

        $response = $this->get("/i/{$invitation->public_token}");

        $response->assertOk();
        $response->assertSee('invitations/photo-1.jpg', false);
    }
}
