<?php

namespace Modules\Landing\Http\Controllers;

use App\Contracts\Templates\TemplateCatalog;
use App\Support\WhatsApp\WhatsAppLinkBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class PublicSiteController extends Controller
{
    public function legal(): View
    {
        return view('landing::legal.legal', [
            'whatsappUrl' => WhatsAppLinkBuilder::make()->link(),
        ]);
    }

    public function privacy(): View
    {
        return view('landing::legal.privacy', [
            'whatsappUrl' => WhatsAppLinkBuilder::make()->link(),
        ]);
    }

    public function robots(): Response
    {
        return response(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Sitemap: '.route('sitemap'),
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(TemplateCatalog $templates): Response
    {
        $urls = collect([
            ['location' => route('landing.index'), 'priority' => '1.0'],
            ['location' => route('templates.index'), 'priority' => '0.9'],
            ['location' => route('legal.mentions'), 'priority' => '0.3'],
            ['location' => route('legal.privacy'), 'priority' => '0.3'],
        ])->merge($templates->sitemap()->map(
            fn ($template) => ['location' => $template->detailUrl, 'priority' => '0.8'],
        ));

        return response()
            ->view('landing::seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
