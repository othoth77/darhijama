<?php

namespace Tests\Feature;

use App\Listeners\RecordPublicPageView;
use App\Listeners\RecordRsvpSubmission;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Listeners\NotifyAdminsOfRsvp;
use Modules\Invitations\Models\Invitation;
use RuntimeException;
use Tests\TestCase;

class ProductionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_rejects_the_default_admin_password(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(RuntimeException::class);
        app(DatabaseSeeder::class)->run();
    }

    public function test_invitation_structured_data_cannot_break_out_of_its_script_element(): void
    {
        $invitation = Invitation::factory()->create([
            'status' => InvitationStatus::Publie,
            'published_at' => now(),
            'groom_name' => '</script><script>alert("xss")</script>',
        ]);

        $this->get(route('invitations.public.show', $invitation->public_token))
            ->assertOk()
            ->assertDontSee('</script><script>alert("xss")</script>', false)
            ->assertSee('\u003C/script\u003E', false);
    }

    public function test_landing_structured_data_is_valid_json_ld(): void
    {
        $content = $this->get(route('landing.index'))->assertOk()->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $matches);
        $data = json_decode($matches[1] ?? '', true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $data['@context']);
        $this->assertSame('Service', $data['@type']);
    }

    public function test_public_responses_receive_security_headers(): void
    {
        $this->get(route('landing.index'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $this->get('https://localhost')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_non_critical_analytics_and_rsvp_notifications_are_queueable(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(RecordPublicPageView::class));
        $this->assertContains(ShouldQueue::class, class_implements(RecordRsvpSubmission::class));
        $this->assertContains(ShouldQueue::class, class_implements(NotifyAdminsOfRsvp::class));
    }

    public function test_media_and_edge_upload_limits_are_aligned_to_one_hundred_megabytes(): void
    {
        $this->assertContains('max:102400', config('media.validation.profiles.file'));
        $this->assertContains('max:102400', config('media.validation.profiles.video'));
        $this->assertContains('max:102400', config('livewire.temporary_file_upload.rules'));

        $nginx = file_get_contents(base_path('notrejour.tn.nginx.conf'));
        $this->assertStringContainsString('client_max_body_size 100M;', $nginx);
    }
}
