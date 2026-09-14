<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mythos\Core\Analytics\Models\WhatsappClickEvent;
use Tests\TestCase;

class AnalyticsTrackingEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_existing_whatsapp_beacon_payload(): void
    {
        $payload = json_encode(['source' => 'hero'], JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/analytics/whatsapp-click',
            server: ['CONTENT_TYPE' => 'text/plain', 'HTTP_USER_AGENT' => 'test-browser'],
            content: $payload,
        )->assertNoContent();

        $event = WhatsappClickEvent::sole();

        $this->assertSame('hero', $event->source);
        $this->assertNotNull($event->visitor_hash);
    }

    public function test_it_rejects_invalid_tracking_data(): void
    {
        $this->postJson('/analytics/whatsapp-click', [
            'source' => str_repeat('a', 65),
        ])->assertUnprocessable();

        $this->assertDatabaseCount('whatsapp_click_events', 0);
    }

    public function test_it_deduplicates_repeated_clicks_from_the_same_visitor_and_source(): void
    {
        $this->withHeader('User-Agent', 'same-browser')
            ->postJson('/analytics/whatsapp-click', ['source' => 'pricing'])
            ->assertNoContent();
        $this->withHeader('User-Agent', 'same-browser')
            ->postJson('/analytics/whatsapp-click', ['source' => 'pricing'])
            ->assertNoContent();

        $this->assertDatabaseCount('whatsapp_click_events', 1);
    }

    public function test_the_tracking_endpoint_is_rate_limited(): void
    {
        for ($request = 1; $request <= 30; $request++) {
            $this->postJson('/analytics/whatsapp-click', ['source' => 'hero'])
                ->assertNoContent();
        }

        $this->postJson('/analytics/whatsapp-click', ['source' => 'hero'])
            ->assertTooManyRequests();
    }
}
