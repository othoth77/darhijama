<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\PublishInvitationService;
use Tests\TestCase;

class PublishInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_publishes_a_draft_invitation(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ]);

        app(PublishInvitationService::class)->execute($invitation);

        $this->assertTrue($invitation->fresh()->isPublished());
    }

    public function test_publishing_twice_does_not_change_published_at(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ]);

        app(PublishInvitationService::class)->execute($invitation);
        $firstPublishedAt = $invitation->fresh()->published_at;

        $this->travel(1)->hours();

        app(PublishInvitationService::class)->execute($invitation->fresh());
        $secondPublishedAt = $invitation->fresh()->published_at;

        $this->assertTrue($firstPublishedAt->equalTo($secondPublishedAt));
    }
}
