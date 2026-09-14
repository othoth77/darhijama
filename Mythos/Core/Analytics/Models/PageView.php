<?php

namespace Mythos\Core\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source',
        'subject_type',
        'subject_id',
        'visitor_hash',
        'deduplication_key',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }
}
