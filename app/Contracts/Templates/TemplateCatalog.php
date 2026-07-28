<?php

namespace App\Contracts\Templates;

use Illuminate\Support\Collection;

interface TemplateCatalog
{
    /**
     * @return Collection<int, TemplateSummary>
     */
    public function featured(int $limit = 4): Collection;

    /**
     * @return Collection<int, TemplateSummary>
     */
    public function sitemap(): Collection;
}
