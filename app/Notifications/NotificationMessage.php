<?php

namespace App\Notifications;

class NotificationMessage
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
        public readonly array $channels = ['database'],
        public readonly ?string $idempotencyKey = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'channels' => $this->channels,
            'idempotency_key' => $this->idempotencyKey,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'],
            $data['title'],
            $data['body'],
            $data['data'] ?? [],
            $data['channels'] ?? ['database'],
            $data['idempotency_key'] ?? null,
        );
    }
}
