<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mythos\Core\Media\Models\Media;

class SessionRecord extends Model
{
    protected $table = 'dar_hijama_session_records';

    protected $fillable = [
        'appointment_id',
        'started_at',
        'completed_at',
        'attendance',
        'general_note',
        'practitioner_note',
        'follow_up_required',
        'recommended_follow_up_date',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'follow_up_required' => 'boolean',
            'recommended_follow_up_date' => 'date',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }
}
