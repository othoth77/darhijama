<?php

namespace Modules\Templates\Services;

use App\Contracts\Templates\TemplateCatalog;
use App\Contracts\Templates\TemplateSummary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Media\Services\MediaService;
use Modules\Templates\Models\Template;

class EloquentTemplateCatalog implements TemplateCatalog
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function featured(int $limit = 4): Collection
    {
        return $this->baseQuery()
            ->limit($limit)
            ->get()
            ->map(fn (Template $template) => $this->summary($template));
    }

    public function sitemap(): Collection
    {
        return $this->baseQuery()
            ->get()
            ->map(fn (Template $template) => $this->summary($template));
    }

    public function paginate(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->baseQuery();

        if (filled($filters['search'] ?? null)) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (filled($filters['category'] ?? null)) {
            $query->whereHas(
                'category',
                fn (Builder $query) => $query->where('slug', $filters['category']),
            );
        }

        match ($filters['sort'] ?? 'featured') {
            'name' => $query->reorder()->orderBy('name'),
            'latest' => $query->reorder()->latest(),
            default => null,
        };

        $paginator = $query->paginate($perPage)->withQueryString();

        return $paginator->through(function (Template $template): Template {
            $template->setAttribute('preview_url', $this->previewUrl($template));

            return $template;
        });
    }

    public function previewUrl(Template $template): ?string
    {
        return $template->preview_image_path
            ? $this->mediaService->url($template->preview_image_path)
            : null;
    }

    private function baseQuery(): Builder
    {
        return Template::query()
            ->active()
            ->with('category')
            ->orderBy('order')
            ->orderBy('name');
    }

    private function summary(Template $template): TemplateSummary
    {
        return new TemplateSummary(
            $template->id,
            $template->name,
            $template->slug,
            $template->category->name,
            $template->description,
            $this->previewUrl($template),
            route('templates.show', $template->slug),
        );
    }
}
