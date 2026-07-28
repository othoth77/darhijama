<?php

namespace Mythos\Core\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use Mythos\Core\Analytics\AnalyticsEventType;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'subject_type',
        'subject_id',
        'source',
        'visitor_hash',
        'deduplication_key',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => AnalyticsEventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
