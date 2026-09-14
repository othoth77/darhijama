<?php

namespace Mythos\Core\Notifications\Contracts;

use Illuminate\Database\Eloquent\Model;
use Mythos\Core\Notifications\Models\NotificationDelivery;
use Mythos\Core\Notifications\NotificationMessage;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;
use Throwable;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface NotificationDispatcher
{
    public function send(Model $recipient, NotificationMessage $message): NotificationDelivery;

    public function deliver(int $deliveryId): void;

    public function markFailed(int $deliveryId, Throwable $exception): void;
}
