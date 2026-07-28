<?php

namespace Modules\Invitations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Invitations\Database\Factories\ProgramStepFactory;

class ProgramStep extends Model
{
    /** @use HasFactory<ProgramStepFactory> */
    use HasFactory;

    protected static function newFactory(): ProgramStepFactory
    {
        return ProgramStepFactory::new();
    }

    protected $fillable = [
        'invitation_id',
        'time',
        'title',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
