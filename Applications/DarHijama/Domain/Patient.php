<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mythos\Core\Media\Models\Media;

/**
 * @property string $first_name
 * @property string $last_name
 * @property string $public_id
 * @property string $reference
 * @property string|null $phone
 * @property string|null $email
 */
class Patient extends Model
{
    use SoftDeletes;

    protected $table = 'dar_hijama_patients';

    protected $fillable = [
        'first_name',
        'last_name',
        'public_id',
        'reference',
        'normalized_phone',
        'gender',
        'phone',
        'secondary_phone',
        'email',
        'date_of_birth',
        'governorate',
        'city',
        'address',
        'preferred_language',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'active',
        'consent_status',
        'consent_at',
        'created_by',
        'updated_by',
        'duplicate_override_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'active' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }
}
