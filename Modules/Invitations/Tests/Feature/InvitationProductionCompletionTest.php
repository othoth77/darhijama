<?php

namespace Modules\Invitations\Tests\Feature;

use DomainException;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Filament\Resources\InvitationResource;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\DuplicateInvitationService;
use Modules\Invitations\Services\InvitationPreviewLinkService;
use Modules\Invitations\Services\InvitationWorkflowService;
use Modules\Invitations\Services\SubmitRsvpService;
use Mythos\Core\Identity\Models\User;
use Mythos\Core\Media\Contracts\MediaManager as MediaService;
use Mythos\Core\Media\Models\Media;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvitationProductionCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_preview_a_draft_from_a_signed_link(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'invitations.manage']);
        $user->givePermissionTo('invitations.manage');
        $invitation = Invitation::factory()->create();

        $this->actingAs($user)
            ->get(app(InvitationPreviewLinkService::class)->temporary($invitation))
            ->assertOk()
            ->assertSee($invitation->groom_name);
    }

    public function test_admin_invitation_editor_exposes_program_media_and_rsvp_management(): void
    {
        $user = User::factory()->create();
        Permission::create(['name' => 'invitations.manage']);
        Permission::create(['name' => 'media.manage']);
        Role::create(['name' => 'admin'])->givePermissionTo(['invitations.manage', 'media.manage']);
        $user->assignRole('admin');
        $invitation = Invitation::factory()->create();
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->actingAs($user)
            ->get(InvitationResource::getUrl('edit', ['record' => $invitation]))
            ->assertOk()
            ->assertSee('Programme')
            ->assertSee('Galerie, audio et vidéo')
            ->assertSee('Réponses RSVP');
    }

    public function test_preview_requires_a_valid_signature_and_authorization(): void
    {
        $invitation = Invitation::factory()->create();
        $user = User::factory()->create();
        $signedUrl = app(InvitationPreviewLinkService::class)->temporary($invitation);

        $this->get($signedUrl)
            ->assertRedirect(route('filament.admin.auth.login'));

        $this->actingAs($user)
            ->get(route('invitations.preview', $invitation->public_token))
            ->assertForbidden();

        $this->actingAs($user)
            ->get($signedUrl)
            ->assertForbidden();
    }

    public function test_lifecycle_transitions_are_centralized_and_invalid_transitions_fail(): void
    {
        $invitation = Invitation::factory()->create();
        $workflow = app(InvitationWorkflowService::class);

        $workflow->publish($invitation);
        $this->assertTrue($invitation->refresh()->isPublished());

        $workflow->archive($invitation);
        $this->assertSame(InvitationStatus::Archive, $invitation->refresh()->status);

        $workflow->restore($invitation);
        $this->assertSame(InvitationStatus::Brouillon, $invitation->refresh()->status);
        $this->assertNull($invitation->published_at);

        $this->expectException(DomainException::class);
        $workflow->archive($invitation);
    }

    public function test_publication_rejects_unsafe_external_urls(): void
    {
        $invitation = Invitation::factory()->create([
            'external_video_url' => 'http://evil.example/video',
        ]);

        $this->expectException(ValidationException::class);
        app(InvitationWorkflowService::class)->publish($invitation);
    }

    public function test_duplication_creates_a_new_draft_with_program_and_media_metadata(): void
    {
        $source = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
            'qr_code_path' => 'qrcodes/source.png',
        ]);
        $source->programSteps()->create(['title' => 'Cérémonie', 'order' => 1]);
        $source->media()->create([
            'disk' => 'public',
            'path' => 'invitations/image.jpg',
            'type' => 'image',
            'order' => 1,
        ]);
        Storage::fake('public');
        Storage::disk('public')->put('invitations/image.jpg', 'image');

        $copy = app(DuplicateInvitationService::class)->execute($source);

        $this->assertNotSame($source->public_token, $copy->public_token);
        $this->assertSame(InvitationStatus::Brouillon, $copy->status);
        $this->assertNull($copy->published_at);
        $this->assertNull($copy->qr_code_path);
        $this->assertCount(1, $copy->programSteps);
        $this->assertCount(1, $copy->media);

        app(MediaService::class)->deleteMedia($copy->media->first());
        Storage::disk('public')->assertExists('invitations/image.jpg');
    }

    public function test_rsvp_is_deduplicated_and_can_be_safely_corrected(): void
    {
        $invitation = Invitation::factory()->create();
        $service = app(SubmitRsvpService::class);

        $first = $service->execute($invitation, [
            'status' => 'present',
            'name' => 'Sami Ben Ali',
            'phone' => '98 123 456',
        ], 'visitor');
        $corrected = $service->execute($invitation, [
            'status' => 'absent',
            'name' => 'Sami Ben Ali',
            'phone' => '98 123 456',
            'comment' => 'Correction',
        ], 'visitor', $first->correction_token);

        $this->assertSame($first->id, $corrected->id);
        $this->assertSame(1, $invitation->rsvpResponses()->count());
        $this->assertSame(2, $corrected->submissions_count);
        $this->assertSame('absent', $corrected->status->value);
    }

    public function test_rsvp_honeypot_and_rate_limit_block_abuse(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);
        $url = route('invitations.public.rsvp', $invitation->public_token);
        $payload = ['status' => 'present', 'name' => 'Sami'];

        $this->from(route('invitations.public.show', $invitation->public_token))
            ->post($url, $payload + ['website' => 'spam.example'])
            ->assertSessionHasErrors('website');

        $rateLimitedInvitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);
        $rateLimitedUrl = route('invitations.public.rsvp', $rateLimitedInvitation->public_token);

        foreach (range(1, 10) as $attempt) {
            $this->post($rateLimitedUrl, $payload)->assertRedirect();
        }

        $this->post($rateLimitedUrl, $payload)->assertTooManyRequests();
    }

    public function test_cache_is_invalidated_and_missing_media_is_ignored(): void
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
            'path' => 'missing.jpg',
            'type' => 'image',
        ]);

        $this->get(route('invitations.public.show', $invitation->public_token))
            ->assertOk()
            ->assertDontSee('missing.jpg');

        $invitation->programSteps()->create(['title' => 'Dîner', 'order' => 1]);

        $this->get(route('invitations.public.show', $invitation->public_token))
            ->assertOk()
            ->assertSee('Dîner');
    }

    public function test_rsvp_submission_creates_an_admin_database_notification(): void
    {
        Permission::create(['name' => 'invitations.manage']);
        $admin = User::factory()->create();
        $admin->givePermissionTo('invitations.manage');
        $invitation = Invitation::factory()->create();

        app(SubmitRsvpService::class)->execute($invitation, [
            'status' => 'present',
            'name' => 'Sami',
        ], 'visitor');

        $this->assertDatabaseHas('notification_deliveries', [
            'recipient_id' => (string) $admin->id,
            'status' => 'delivered',
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }
}
