<?php

namespace App\Analytics\Models;

use App\Analytics\AnalyticsEventType;
use Illuminate\Database\Eloquent\Model;

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
