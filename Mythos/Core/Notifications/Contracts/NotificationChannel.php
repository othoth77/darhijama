<?php

namespace Mythos\Core\Notifications\Contracts;

use Illuminate\Database\Eloquent\Model;
use Mythos\Core\Notifications\NotificationMessage;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface NotificationChannel
{
    public function send(Model $recipient, NotificationMessage $message): void;
}
