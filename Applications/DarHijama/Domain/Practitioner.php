<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $name
 */
class Practitioner extends Model
{
    use SoftDeletes;

    protected $table = 'dar_hijama_practitioners';

    protected $fillable = [
        'name',
        'user_id',
        'email',
        'phone',
        'license_number',
        'active',
        'maximum_daily_appointments',
        'appointment_duration_minutes',
        'preparation_buffer_minutes',
        'travel_buffer_minutes',
        'home_visits',
        'service_areas',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'home_visits' => 'boolean',
            'service_areas' => 'array',
        ];
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PractitionerSchedule::class);
    }

    public function unavailability(): HasMany
    {
        return $this->hasMany(PractitionerUnavailability::class);
    }
}
