<?php

namespace App\Notifications\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'idempotency_key',
        'message',
        'status',
        'attempts',
        'failed_at',
        'failure_reason',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'message' => 'array',
            'failed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }
}
