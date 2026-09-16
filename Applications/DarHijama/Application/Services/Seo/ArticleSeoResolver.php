<?php

namespace Applications\DarHijama\Application\Services\Seo;

use Applications\DarHijama\Domain\Article;
use DOMDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The single source of truth for "what does this article's SEO actually
 * render as" — used by both the Filament SEO preview panel and the public
 * Blade views, so the admin never sees a preview that lies about what
 * visitors and search engines will get.
 *
 * Every method returns a ResolvedSeoField carrying whether the value is the
 * editor's own input or a generated fallback (see SEO FALLBACK SYSTEM in
 * the design brief) — nothing here silently substitutes without saying so.
 */
class ArticleSeoResolver
{
    private const SITE_SUFFIX = 'دار الحجامة';

    public function seoTitle(Article $article): ResolvedSeoField
    {
        if (filled($article->seo_title)) {
            return new ResolvedSeoField($article->seo_title, isAuto: false);
        }

        return new ResolvedSeoField(
            trim($article->title).' | '.self::SITE_SUFFIX,
            isAuto: true,
        );
    }

    public function metaDescription(Article $article): ResolvedSeoField
    {
        if (filled($article->meta_description)) {
            return new ResolvedSeoField($article->meta_description, isAuto: false);
        }

        $source = filled($article->excerpt) ? $article->excerpt : $this->stripHtml($article->content);
        $clean = trim(preg_replace('/\s+/u', ' ', $source) ?? '');

        return new ResolvedSeoField(
            $clean === '' ? null : Str::limit($clean, 157, '…'),
            isAuto: true,
        );
    }

    public function canonicalUrl(Article $article): ResolvedSeoField
    {
        if (filled($article->canonical_url)) {
            return new ResolvedSeoField($article->canonical_url, isAuto: false);
        }

        return new ResolvedSeoField(
            filled($article->slug) ? $this->articleUrl($article->slug) : null,
            isAuto: true,
        );
    }

    public function ogImage(Article $article): ResolvedSeoField
    {
        if (filled($article->og_image)) {
            return new ResolvedSeoField($this->assetUrl($article->og_image), isAuto: false);
        }

        return new ResolvedSeoField(
            filled($article->featured_image) ? $this->assetUrl($article->featured_image) : null,
            isAuto: true,
        );
    }

    public function socialTitle(Article $article): ResolvedSeoField
    {
        if (filled($article->social_title)) {
            return new ResolvedSeoField($article->social_title, isAuto: false);
        }

        $seoTitle = $this->seoTitle($article);

        return new ResolvedSeoField($seoTitle->value, isAuto: true);
    }

    public function socialDescription(Article $article): ResolvedSeoField
    {
        if (filled($article->social_description)) {
            return new ResolvedSeoField($article->social_description, isAuto: false);
        }

        $metaDescription = $this->metaDescription($article);

        return new ResolvedSeoField($metaDescription->value, isAuto: true);
    }

    /**
     * Real diagnostics only — no invented "SEO score". Each check reports
     * a plain state the editor can act on directly.
     *
     * @return array<string, mixed>
     */
    public function diagnostics(Article $article): array
    {
        $title = $this->seoTitle($article)->value ?? '';
        $description = $this->metaDescription($article)->value ?? '';
        $canonical = $this->canonicalUrl($article)->value;
        $contentStats = $this->analyzeContent((string) $article->content, $article->slug);

        return [
            'title' => [
                'length' => mb_strlen($title),
                'state' => match (true) {
                    $title === '' => 'missing',
                    mb_strlen($title) < 30 => 'too_short',
                    mb_strlen($title) > 60 => 'too_long',
                    default => 'acceptable',
                },
            ],
            'description' => [
                'length' => mb_strlen($description),
                'state' => match (true) {
                    $description === '' => 'missing',
                    mb_strlen($description) < 70 => 'short',
                    mb_strlen($description) > 160 => 'long',
                    default => 'acceptable',
                },
            ],
            'slug' => [
                'value' => $article->slug,
                'state' => match (true) {
                    blank($article->slug) => 'missing',
                    ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $article->slug) => 'invalid',
                    $this->slugIsDuplicate($article) => 'duplicate',
                    default => 'valid',
                },
            ],
            'canonical' => [
                'value' => $canonical,
                'state' => $canonical !== null && filter_var($canonical, FILTER_VALIDATE_URL) !== false
                    ? 'valid' : 'invalid',
            ],
            'featured_image' => [
                'exists' => filled($article->featured_image),
                'has_alt' => filled($article->featured_image_alt),
            ],
            'content' => $contentStats,
            'indexability' => [
                'index' => ! $article->noindex,
                'follow' => ! $article->nofollow,
            ],
        ];
    }

    /** @return array<string, int> */
    private function analyzeContent(string $html, ?string $currentSlug): array
    {
        $stats = [
            'h1_count' => 0,
            'h2_count' => 0,
            'h3_count' => 0,
            'images' => 0,
            'images_missing_alt' => 0,
            'internal_links' => 0,
            'external_links' => 0,
        ];

        if (trim($html) === '') {
            return $stats;
        }

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING,
        );
        libxml_clear_errors();

        $stats['h1_count'] = $dom->getElementsByTagName('h1')->length;
        $stats['h2_count'] = $dom->getElementsByTagName('h2')->length;
        $stats['h3_count'] = $dom->getElementsByTagName('h3')->length;

        foreach ($dom->getElementsByTagName('img') as $img) {
            $stats['images']++;
            if (trim((string) $img->getAttribute('alt')) === '') {
                $stats['images_missing_alt']++;
            }
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);

        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = trim((string) $a->getAttribute('href'));
            if ($href === '' || str_starts_with($href, '#')) {
                continue;
            }
            $linkHost = parse_url($href, PHP_URL_HOST);
            if ($linkHost === null || $linkHost === $host) {
                $stats['internal_links']++;
            } else {
                $stats['external_links']++;
            }
        }

        return $stats;
    }

    private function slugIsDuplicate(Article $article): bool
    {
        if (blank($article->slug)) {
            return false;
        }

        return Article::query()
            ->where('slug', $article->slug)
            ->when($article->exists, fn ($q) => $q->whereKeyNot($article->getKey()))
            ->exists();
    }

    private function stripHtml(string $html): string
    {
        return trim(strip_tags($html));
    }

    private function articleUrl(string $slug): string
    {
        return rtrim((string) config('app.url'), '/').'/articles/'.$slug;
    }

    /**
     * Featured/OG images live on the `public` disk (see ArticleResource's
     * FileUpload), which is served from /storage — asset() would point at the
     * document root instead and every social preview would 404.
     */
    private function assetUrl(string $path): string
    {
        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : Storage::disk('public')->url($path);
    }
}
