<?php

namespace Mythos\Core\Media\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mythos\Core\Media\Database\Factories\MediaFactory;

/**
 * Ligne de métadonnées d'un fichier (image, QR code...) rattaché à un Template ou
 * une Invitation. Ce modèle ne gère jamais le fichier physique lui-même en
 * Phase 1 : la suppression d'une ligne ne supprime pas le fichier sur disque
 * (voir Observers Template/Invitation, PHASE_1.md §8).
 */
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    protected static function newFactory(): MediaFactory
    {
        return MediaFactory::new();
    }

    protected $table = 'media';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'disk',
        'path',
        'type',
        'original_name',
        'mime_type',
        'size',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
