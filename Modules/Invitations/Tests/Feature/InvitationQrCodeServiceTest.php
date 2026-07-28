<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\InvitationPublicLinkService;
use Modules\Invitations\Services\InvitationQrCodeService;
use Tests\TestCase;

class InvitationQrCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_a_missing_cached_file_is_recreated_at_the_existing_path(): void
    {
        $invitation = Invitation::factory()->create([
            'qr_code_path' => 'legacy/kept-path.png',
        ]);

        $stored = app(InvitationQrCodeService::class)->getOrCreate($invitation);

        $this->assertSame('legacy/kept-path.png', $stored->path);
        $this->assertSame('legacy/kept-path.png', $invitation->fresh()->qr_code_path);
        Storage::disk('public')->assertExists('legacy/kept-path.png');
    }

    public function test_regeneration_preserves_the_token_and_cached_path(): void
    {
        $invitation = Invitation::factory()->create();
        $service = app(InvitationQrCodeService::class);
        $first = $service->getOrCreate($invitation);
        $token = $invitation->public_token;

        Storage::disk('public')->put($first->path, 'corrupted');
        $regenerated = $service->regenerate($invitation);

        $this->assertSame($token, $invitation->fresh()->public_token);
        $this->assertSame($first->path, $regenerated->path);
        $this->assertSame($first->path, $invitation->fresh()->qr_code_path);
        $this->assertStringStartsWith(
            "\x89PNG",
            Storage::disk('public')->get($regenerated->path),
        );
        $this->assertSame(
            [$regenerated->path],
            Storage::disk('public')->allFiles('qrcodes'),
        );
    }

    public function test_invalidation_deletes_the_file_and_clears_the_cached_path(): void
    {
        $invitation = Invitation::factory()->create();
        $service = app(InvitationQrCodeService::class);
        $stored = $service->getOrCreate($invitation);

        $service->invalidate($invitation);

        Storage::disk('public')->assertMissing($stored->path);
        $this->assertNull($invitation->fresh()->qr_code_path);
    }

    public function test_all_invitation_public_links_preserve_existing_routes_and_token(): void
    {
        $invitation = Invitation::factory()->create();
        $links = app(InvitationPublicLinkService::class);

        $this->assertSame(
            url("/i/{$invitation->public_token}"),
            $links->show($invitation),
        );
        $this->assertSame(
            url("/i/{$invitation->public_token}/qr"),
            $links->qr($invitation),
        );
        $this->assertSame(
            url("/i/{$invitation->public_token}/rsvp"),
            $links->rsvp($invitation),
        );
    }
}
