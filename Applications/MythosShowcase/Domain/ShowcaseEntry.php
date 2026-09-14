<?php

namespace Applications\MythosShowcase\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mythos\Core\Media\Models\Media;

/**
 * @property string $title
 * @property string|null $message
 */
class ShowcaseEntry extends Model
{
    protected $table = 'mythos_showcase_entries';

    protected $fillable = [
        'title',
        'message',
    ];

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }
}
