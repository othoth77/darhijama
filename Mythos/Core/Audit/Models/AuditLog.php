<?php

namespace Mythos\Core\Audit\Models;

use Illuminate\Database\Eloquent\Model;
use Mythos\Core\Audit\AuditAction;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'changes',
        'ip_address',
        'user_agent',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'changes' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
