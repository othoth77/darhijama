<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PractitionerUnavailability extends Model
{
    public $timestamps = false;

    protected $table = 'dar_hijama_practitioner_unavailability';

    protected $fillable = [
        'practitioner_id',
        'starts_at',
        'ends_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function practitioner(): BelongsTo
    {
        return $this->belongsTo(Practitioner::class);
    }
}
