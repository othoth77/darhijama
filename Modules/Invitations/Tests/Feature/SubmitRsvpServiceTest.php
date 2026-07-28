<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Enums\RsvpStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\SubmitRsvpService;
use Tests\TestCase;

class SubmitRsvpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_present_rsvp_response(): void
    {
        $invitation = Invitation::factory()->create();

        $response = app(SubmitRsvpService::class)->execute($invitation, [
            'status' => 'present',
            'name' => 'Sami Ben Ali',
            'phone' => '21698123456',
            'guests_count' => 2,
            'comment' => 'Avec grand plaisir !',
        ]);

        $this->assertSame(RsvpStatus::Present, $response->status);
        $this->assertSame($invitation->id, $response->invitation_id);
        $this->assertSame(2, $response->guests_count);
        $this->assertDatabaseHas('rsvp_responses', [
            'invitation_id' => $invitation->id,
            'name' => 'Sami Ben Ali',
            'status' => 'present',
        ]);
    }

    public function test_it_creates_an_absent_rsvp_response(): void
    {
        $invitation = Invitation::factory()->create();

        $response = app(SubmitRsvpService::class)->execute($invitation, [
            'status' => 'absent',
            'name' => 'Nour Trabelsi',
        ]);

        $this->assertSame(RsvpStatus::Absent, $response->status);
    }

    public function test_guests_count_defaults_to_zero_when_not_provided(): void
    {
        $invitation = Invitation::factory()->create();

        $response = app(SubmitRsvpService::class)->execute($invitation, [
            'status' => 'present',
            'name' => 'Amine Gharbi',
        ]);

        $this->assertSame(0, $response->guests_count);
    }

    public function test_an_invitation_can_have_multiple_rsvp_responses(): void
    {
        $invitation = Invitation::factory()->create();
        $service = app(SubmitRsvpService::class);

        $service->execute($invitation, ['status' => 'present', 'name' => 'Invité 1']);
        $service->execute($invitation, ['status' => 'absent', 'name' => 'Invité 2']);

        $this->assertSame(2, $invitation->rsvpResponses()->count());
    }
}
