<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PractitionerSchedule extends Model
{
    public $timestamps = false;

    protected $table = 'dar_hijama_practitioner_schedules';

    protected $fillable = [
        'practitioner_id',
        'day_of_week',
        'starts_at',
        'ends_at',
        'break_starts_at',
        'break_ends_at',
        'service_area',
        'home_visits',
    ];

    protected function casts(): array
    {
        return ['home_visits' => 'boolean'];
    }

    public function practitioner(): BelongsTo
    {
        return $this->belongsTo(Practitioner::class);
    }
}
