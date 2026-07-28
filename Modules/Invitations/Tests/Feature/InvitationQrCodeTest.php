<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Tests\TestCase;

/**
 * Génération à la demande du QR Code (endroid/qr-code), mise en cache via la
 * colonne invitations.qr_code_path (déjà existante depuis Phase 1, jusqu'ici
 * inutilisée — aucune colonne dupliquée créée).
 */
class InvitationQrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_and_serves_a_qr_code_for_a_published_invitation(): void
    {
        Storage::fake('public');

        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
            'qr_code_path' => null,
        ]);

        $response = $this->get("/i/{$invitation->public_token}/qr");

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');

        $invitation->refresh();
        $this->assertNotNull($invitation->qr_code_path);
        Storage::disk('public')->assertExists($invitation->qr_code_path);
    }

    public function test_it_returns_404_for_a_non_published_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ]);

        $response = $this->get("/i/{$invitation->public_token}/qr");

        $response->assertNotFound();
    }
}
