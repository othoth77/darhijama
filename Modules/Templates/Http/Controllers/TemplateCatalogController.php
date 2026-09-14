<?php

namespace Modules\Templates\Http\Controllers;

use App\Events\PublicPageViewed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Templates\Models\Template;
use Modules\Templates\Models\TemplateCategory;
use Modules\Templates\Services\EloquentTemplateCatalog;
use Mythos\Core\Analytics\VisitorFingerprint;
use Mythos\Core\WhatsApp\WhatsAppLinkBuilder;

class TemplateCatalogController extends Controller
{
    public function __construct(
        private readonly EloquentTemplateCatalog $catalog,
        private readonly VisitorFingerprint $fingerprint,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:featured,name,latest'],
        ]);

        PublicPageViewed::dispatch('templates', $this->fingerprint->fromRequest($request));

        return view('templates::public.index', [
            'templates' => $this->catalog->paginate($filters),
            'categories' => TemplateCategory::query()
                ->whereHas('templates', fn ($query) => $query->active())
                ->orderBy('order')
                ->orderBy('name')
                ->get(),
            'filters' => $filters,
            'whatsappUrl' => WhatsAppLinkBuilder::make()->link(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $template = Template::query()
            ->active()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        PublicPageViewed::dispatch(
            'template',
            $this->fingerprint->fromRequest($request),
            'template',
            $template->id,
        );

        return view('templates::public.show', [
            'template' => $template,
            'previewUrl' => $this->catalog->previewUrl($template),
            'demo' => $this->demoData($template),
            'whatsappUrl' => WhatsAppLinkBuilder::make()->link(
                "Bonjour Notre Jour, je souhaite commander le modèle {$template->name}."
            ),
        ]);
    }

    private function demoData(Template $template): array
    {
        return array_replace([
            'groom_name' => 'Karim',
            'bride_name' => 'Leila',
            'date' => '21 juin 2027',
            'venue' => 'Dar El Jeld, Tunis',
            'message' => 'Nous serions heureux de partager ce moment unique avec vous.',
        ], array_intersect_key(
            $template->demo_data ?? [],
            array_flip(['groom_name', 'bride_name', 'date', 'venue', 'message']),
        ));
    }
}
