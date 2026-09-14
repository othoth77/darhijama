<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Tests\TestCase;

class InvitationRsvpControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_submit_an_rsvp_for_a_published_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        $response = $this->post("/i/{$invitation->public_token}/rsvp", [
            'status' => 'present',
            'name' => 'Sami Ben Ali',
            'phone' => '21698123456',
            'guests_count' => 3,
            'comment' => 'Hâte d\'y être !',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('rsvp_success', true);
        $this->assertDatabaseHas('rsvp_responses', [
            'invitation_id' => $invitation->id,
            'name' => 'Sami Ben Ali',
            'status' => 'present',
            'guests_count' => 3,
        ]);
    }

    public function test_rsvp_submission_requires_a_name(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        $response = $this->post("/i/{$invitation->public_token}/rsvp", [
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('rsvp_responses', 0);
    }

    public function test_rsvp_submission_requires_a_valid_status(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        $response = $this->post("/i/{$invitation->public_token}/rsvp", [
            'status' => 'peut-etre',
            'name' => 'Sami Ben Ali',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('rsvp_responses', 0);
    }

    public function test_rsvp_submission_rejects_an_invalid_phone_number(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        $this->post("/i/{$invitation->public_token}/rsvp", [
            'status' => 'present',
            'name' => 'Sami Ben Ali',
            'phone' => 'call me tomorrow',
        ])->assertSessionHasErrors('phone');

        $this->assertDatabaseCount('rsvp_responses', 0);
    }

    public function test_rsvp_submission_fails_for_a_non_published_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ]);

        $response = $this->post("/i/{$invitation->public_token}/rsvp", [
            'status' => 'present',
            'name' => 'Sami Ben Ali',
        ]);

        $response->assertNotFound();
        $this->assertDatabaseCount('rsvp_responses', 0);
    }
}
