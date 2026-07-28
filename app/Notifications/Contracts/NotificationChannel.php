<?php

namespace App\Notifications\Contracts;

use App\Notifications\NotificationMessage;
use Illuminate\Database\Eloquent\Model;

interface NotificationChannel
{
    public function send(Model $recipient, NotificationMessage $message): void;
}
