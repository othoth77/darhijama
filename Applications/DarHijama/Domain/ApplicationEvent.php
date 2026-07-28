<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;

class ApplicationEvent extends Model
{
    public $timestamps = false;

    protected $table = 'dar_hijama_analytics_events';

    protected $fillable = [
        'event',
        'subject_type',
        'subject_id',
        'actor_id',
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
