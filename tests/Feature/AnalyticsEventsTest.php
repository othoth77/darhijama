<?php

namespace Tests\Feature;

use App\Analytics\AnalyticsEventType;
use App\Analytics\Models\AnalyticsEvent;
use App\Analytics\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\PublishInvitationService;
use Modules\Invitations\Services\SubmitRsvpService;
use Modules\Orders\Services\CreateOrderService;
use Tests\TestCase;

class AnalyticsEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_views_are_recorded_without_duplicates(): void
    {
        $this->withHeader('User-Agent', 'same-browser')->get('/')->assertOk();
        $this->withHeader('User-Agent', 'same-browser')->get('/')->assertOk();

        $event = PageView::sole();

        $this->assertSame('landing', $event->source);
        $this->assertNull($event->subject_id);
    }

    public function test_public_and_invitation_views_are_recorded_without_duplicates(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
        ]);

        $this->withHeader('User-Agent', 'same-browser')->get("/i/{$invitation->public_token}")->assertOk();
        $this->withHeader('User-Agent', 'same-browser')->get("/i/{$invitation->public_token}")->assertOk();

        $this->assertSame(1, PageView::where('source', 'invitation')->count());
        $this->assertSame(1, AnalyticsEvent::where('type', AnalyticsEventType::InvitationView)->count());
    }

    public function test_invitation_publication_is_recorded_once(): void
    {
        $invitation = Invitation::factory()->create();
        $service = app(PublishInvitationService::class);

        $service->execute($invitation);
        $service->execute($invitation->fresh());

        $this->assertSame(1, AnalyticsEvent::where('type', AnalyticsEventType::InvitationPublished)->count());
    }

    public function test_rsvp_submission_is_recorded_without_personal_data(): void
    {
        $invitation = Invitation::factory()->create();

        $response = app(SubmitRsvpService::class)->execute($invitation, [
            'status' => 'present',
            'name' => 'Private Name',
            'phone' => '99123456',
            'comment' => 'Private comment',
        ]);

        $event = AnalyticsEvent::where('type', AnalyticsEventType::RsvpSubmitted)->sole();

        $this->assertSame((string) $invitation->id, $event->subject_id);
        $this->assertSame(['response_id' => $response->id], $event->metadata);
        $this->assertStringNotContainsString('Private', json_encode($event->toArray()));
        $this->assertStringNotContainsString('99123456', json_encode($event->toArray()));
    }

    public function test_order_creation_is_recorded_once_without_customer_data(): void
    {
        $order = app(CreateOrderService::class)->execute([
            'client' => ['name' => 'Private Client', 'whatsapp_phone' => '98123456'],
        ]);

        $event = AnalyticsEvent::where('type', AnalyticsEventType::OrderCreated)->sole();

        $this->assertSame((string) $order->id, $event->subject_id);
        $this->assertNull($event->metadata);
    }
}
