<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property AppointmentStatus $status
 * @property string $public_id
 * @property string $reference
 */
class Appointment extends Model
{
    use SoftDeletes;

    protected $table = 'dar_hijama_appointments';

    protected $fillable = [
        'patient_id',
        'practitioner_id',
        'public_id',
        'reference',
        'type',
        'visit_mode',
        'starts_at',
        'ends_at',
        'expected_duration_minutes',
        'governorate',
        'city',
        'address',
        'location_notes',
        'status',
        'internal_notes',
        'patient_visible_notes',
        'source',
        'cancellation_reason',
        'rescheduling_reason',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'created_by',
        'updated_by',
        'original_appointment_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => AppointmentStatus::class,
            'type' => AppointmentType::class,
            'source' => AppointmentSource::class,
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function practitioner(): BelongsTo
    {
        return $this->belongsTo(Practitioner::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(AppointmentTransition::class);
    }

    public function sessionRecord(): HasOne
    {
        return $this->hasOne(SessionRecord::class);
    }

    public function originalAppointment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'original_appointment_id');
    }

    public function followUp(): HasOne
    {
        return $this->hasOne(self::class, 'original_appointment_id');
    }
}
