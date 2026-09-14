<?php

namespace Mythos\Core\Notifications;

use Illuminate\Database\Eloquent\Model;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Mythos\Core\Notifications\Contracts\NotificationQueue;
use Mythos\Core\Notifications\Models\NotificationDelivery;
use Throwable;

class NotificationService implements NotificationDispatcher
{
    public function __construct(
        private readonly NotificationQueue $queue,
        private readonly NotificationChannelManager $channels,
    ) {}

    public function send(Model $recipient, NotificationMessage $message): NotificationDelivery
    {
        $key = hash('sha256', $message->idempotencyKey ?? implode('|', [
            $recipient->getMorphClass(),
            $recipient->getKey(),
            $message->type,
            json_encode($message->data, JSON_THROW_ON_ERROR),
        ]));

        $delivery = NotificationDelivery::firstOrCreate(
            ['idempotency_key' => $key],
            [
                'recipient_type' => $recipient::class,
                'recipient_id' => (string) $recipient->getKey(),
                'message' => $message->toArray(),
                'status' => 'pending',
            ],
        );

        if ($delivery->wasRecentlyCreated) {
            $this->queue->dispatch($delivery->id);
        }

        return $delivery;
    }

    public function deliver(int $deliveryId): void
    {
        $delivery = NotificationDelivery::findOrFail($deliveryId);

        if ($delivery->status === 'delivered') {
            return;
        }

        $delivery->increment('attempts');
        $recipient = $delivery->recipient_type::findOrFail($delivery->recipient_id);
        $message = NotificationMessage::fromArray($delivery->message);

        foreach ($message->channels as $channel) {
            $channelMessage = new NotificationMessage(
                $message->type,
                $message->title,
                $message->body,
                $message->data,
                [$channel],
                $delivery->idempotency_key.'|'.$channel,
            );
            $this->channels->channel($channel)->send($recipient, $channelMessage);
        }

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => now(),
            'failed_at' => null,
            'failure_reason' => null,
        ]);
    }

    public function markFailed(int $deliveryId, Throwable $exception): void
    {
        NotificationDelivery::whereKey($deliveryId)->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => mb_substr($exception->getMessage(), 0, 2000),
        ]);
    }
}
