<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentTransition extends Model
{
    public $timestamps = false;

    protected $table = 'dar_hijama_appointment_transitions';

    protected $fillable = [
        'appointment_id',
        'from_status',
        'to_status',
        'actor_id',
        'reason',
        'notes',
        'channel',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => AppointmentStatus::class,
            'to_status' => AppointmentStatus::class,
            'occurred_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
