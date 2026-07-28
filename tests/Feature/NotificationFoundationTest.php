<?php

namespace Tests\Feature;

use App\Events\NotificationRequested;
use App\Models\User;
use App\Notifications\Contracts\NotificationQueue;
use App\Notifications\Jobs\SendNotification;
use App\Notifications\NotificationChannelManager;
use App\Notifications\NotificationMessage;
use App\Notifications\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class NotificationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_are_queued_once_by_idempotency_key(): void
    {
        Queue::fake();
        $recipient = User::factory()->create();
        $message = $this->message('order-1');

        $first = app(NotificationService::class)->send($recipient, $message);
        $second = app(NotificationService::class)->send($recipient, $message);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('notification_deliveries', 1);
        Queue::assertPushed(SendNotification::class, 1);
    }

    public function test_database_notifications_are_delivered_idempotently(): void
    {
        $this->app->bind(NotificationQueue::class, fn () => new class implements NotificationQueue
        {
            public function dispatch(int $deliveryId): void {}
        });

        $recipient = User::factory()->create();
        $delivery = app(NotificationService::class)->send($recipient, $this->message('database-1'));

        app(NotificationService::class)->deliver($delivery->id);
        app(NotificationService::class)->deliver($delivery->id);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame('delivered', $delivery->fresh()->status);
        $this->assertSame(1, $delivery->fresh()->attempts);
        $this->assertCount(1, $recipient->notifications);
    }

    public function test_notification_requests_use_the_event_driven_queue(): void
    {
        Queue::fake();
        $recipient = User::factory()->create();

        NotificationRequested::dispatch(
            User::class,
            $recipient->id,
            $this->message('event-1'),
        );

        $this->assertDatabaseCount('notification_deliveries', 1);
        Queue::assertPushed(SendNotification::class);
    }

    public function test_disabled_future_channels_fail_safely_and_can_be_recorded(): void
    {
        $this->app->bind(NotificationQueue::class, fn () => new class implements NotificationQueue
        {
            public function dispatch(int $deliveryId): void {}
        });

        $recipient = User::factory()->create();
        $delivery = app(NotificationService::class)->send(
            $recipient,
            new NotificationMessage(
                'future',
                'Future',
                'Not active',
                channels: ['database', 'mail'],
                idempotencyKey: 'future-1',
            ),
        );

        try {
            app(NotificationService::class)->deliver($delivery->id);
            $this->fail('The disabled channel should fail.');
        } catch (RuntimeException $exception) {
            app(NotificationService::class)->markFailed($delivery->id, $exception);
        }

        try {
            app(NotificationService::class)->deliver($delivery->id);
        } catch (RuntimeException $exception) {
            app(NotificationService::class)->markFailed($delivery->id, $exception);
        }

        $delivery->refresh();
        $this->assertSame('failed', $delivery->status);
        $this->assertSame(2, $delivery->attempts);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertNotNull($delivery->failed_at);
        $this->assertStringContainsString('mail', $delivery->failure_reason);
    }

    public function test_job_retry_and_channel_foundations_are_configured(): void
    {
        $job = new SendNotification(123);

        $this->assertSame(3, $job->tries);
        $this->assertSame([10, 60, 300], $job->backoff);
        $this->assertNotNull(app(NotificationChannelManager::class)->channel('database'));
        $this->assertNotNull(app(NotificationChannelManager::class)->channel('whatsapp'));
        $this->assertNotNull(app(NotificationChannelManager::class)->channel('push'));
    }

    private function message(string $key): NotificationMessage
    {
        return new NotificationMessage(
            'order.created',
            'Commande créée',
            'La commande est prête.',
            ['order_id' => 1],
            ['database'],
            $key,
        );
    }
}
