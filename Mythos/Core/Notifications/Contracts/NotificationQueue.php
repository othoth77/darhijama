<?php

namespace Mythos\Core\Notifications\Contracts;

use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface NotificationQueue
{
    public function dispatch(int $deliveryId): void;
}
