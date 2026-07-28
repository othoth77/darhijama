<?php

namespace Mythos\Core\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappClickEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source',
        'invitation_id',
        'visitor_hash',
        'deduplication_key',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
