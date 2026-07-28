<?php

namespace Modules\Templates\Observers;

use Modules\Media\Services\MediaService;
use Modules\Templates\Models\Template;

/**
 * Supprime uniquement les lignes Media rattachées (jamais les fichiers physiques
 * sur disque) — voir PHASE_1.md §8. Suppression de fichiers réservée à un futur
 * Le comportement historique est conservé explicitement via MediaService.
 */
class TemplateObserver
{
    public function deleting(Template $template): void
    {
        app(MediaService::class)->deleteFor($template, deleteFiles: false);
    }
}
