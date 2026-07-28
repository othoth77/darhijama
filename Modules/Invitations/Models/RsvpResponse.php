<?php

namespace Modules\Invitations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Invitations\Database\Factories\RsvpResponseFactory;
use Modules\Invitations\Enums\RsvpStatus;

class RsvpResponse extends Model
{
    /** @use HasFactory<RsvpResponseFactory> */
    use HasFactory;

    protected static function newFactory(): RsvpResponseFactory
    {
        return RsvpResponseFactory::new();
    }

    protected $fillable = [
        'invitation_id',
        'identity_hash',
        'correction_token',
        'status',
        'name',
        'phone',
        'guests_count',
        'comment',
        'submissions_count',
        'last_submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RsvpStatus::class,
            'guests_count' => 'integer',
            'submissions_count' => 'integer',
            'last_submitted_at' => 'datetime',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
