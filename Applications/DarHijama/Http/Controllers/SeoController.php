<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleCategory;
use Illuminate\Http\Response;

/**
 * Deliberately separate from Modules/Landing/Http/Controllers/PublicSiteController
 * (the shared sitemap/robots used by other applications on this same
 * codebase, e.g. Notre Jour): these two routes are registered domain-scoped
 * to config('applications.dar-hijama.public_hosts.*') in
 * Applications/DarHijama/routes/web.php, so they only ever answer for
 * darhijama.tn and never touch the shared module or another app's output.
 */
class SeoController
{
    public function sitemap(): Response
    {
        $host = rtrim((string) config('applications.dar-hijama.public_hosts.primary'), '/');
        $base = 'https://'.$host;

        $urls = collect([
            ['loc' => $base.'/', 'priority' => '1.0'],
            ['loc' => $base.'/articles', 'priority' => '0.8'],
        ]);

        ArticleCategory::query()
            ->whereHas('articles', fn ($q) => $q->published())
            ->get()
            ->each(function (ArticleCategory $category) use (&$urls, $base): void {
                $urls->push(['loc' => $base.'/articles/category/'.$category->slug, 'priority' => '0.6']);
            });

        Article::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Article $article) use (&$urls, $base): void {
                $urls->push([
                    'loc' => $base.'/articles/'.$article->slug,
                    'priority' => '0.7',
                    'lastmod' => $article->updated_at?->toAtomString(),
                ]);
            });

        // Built here, not written literally in the Blade file: see the fixed
        // Modules/Landing sitemap bug — an inline XML processing instruction
        // inside a Blade raw echo defeats Blade's own compiler.
        $xmlDeclaration = '<?xml version="1.0" encoding="UTF-8"?>';

        return response()
            ->view('dar-hijama::public.sitemap', ['urls' => $urls, 'xmlDeclaration' => $xmlDeclaration])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $host = (string) config('applications.dar-hijama.public_hosts.primary');

        return response(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Sitemap: https://'.$host.'/sitemap.xml',
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
