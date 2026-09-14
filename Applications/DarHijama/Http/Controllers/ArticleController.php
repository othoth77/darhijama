<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\Seo\ArticleSeoResolver;
use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleCategory;
use Applications\DarHijama\Domain\ArticleRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Mythos\Core\WhatsApp\WhatsAppLinkBuilder;
use Symfony\Component\HttpFoundation\Response;

class ArticleController
{
    public function __construct(
        private readonly ArticleSeoResolver $seo,
    ) {}

    public function index(): View
    {
        $articles = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('dar-hijama::public.articles.index', array_merge(
            $this->indexLayoutData($articles, null),
            [
                'articles' => $articles,
                'categories' => $this->categoriesWithPublishedArticles(),
                'activeCategory' => null,
            ],
        ));
    }

    public function category(ArticleCategory $category): View
    {
        $articles = $category->articles()
            ->published()
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('dar-hijama::public.articles.index', array_merge(
            $this->indexLayoutData($articles, $category),
            [
                'articles' => $articles,
                'categories' => $this->categoriesWithPublishedArticles(),
                'activeCategory' => $category,
            ],
        ));
    }

    /**
     * No implicit route-model-binding here on purpose: an unpublished
     * article must 404 for the public even though the row exists (draft/
     * scheduled content is never leaked), and a slug that changed must 301
     * to its current one instead of 404ing — both need explicit handling.
     */
    public function show(string $slug): View|RedirectResponse|Response
    {
        $article = Article::query()->where('slug', $slug)->published()->first();

        if ($article) {
            return $this->renderArticle($article);
        }

        $redirect = ArticleRedirect::query()->where('old_slug', $slug)->first();

        if ($redirect && $redirect->article && $redirect->article->isPubliclyVisible()) {
            return redirect()->route('dar-hijama.articles.show', $redirect->article->slug, status: 301);
        }

        abort(404);
    }

    private function renderArticle(Article $article): View|Response
    {
        $related = Article::query()
            ->published()
            ->whereKeyNot($article->id)
            ->where(function ($q) use ($article) {
                $q->where('category_id', $article->category_id)
                    ->orWhereHas('tags', fn ($t) => $t->whereIn(
                        'dar_hijama_article_tags.id',
                        $article->tags->pluck('id'),
                    ));
            })
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $canonical = $this->seo->canonicalUrl($article)->value ?? $this->articleUrl($article->slug);

        $view = view('dar-hijama::public.articles.show', [
            'article' => $article,
            'related' => $related,
            'whatsappBookingUrl' => $this->whatsappUrl($article),
            'seoTitle' => $this->seo->seoTitle($article)->value ?? $article->title,
            'metaDescription' => $this->seo->metaDescription($article)->value ?? '',
            'canonicalUrl' => $canonical,
            'noindex' => $article->noindex,
            'nofollow' => $article->nofollow,
            'ogType' => 'article',
            'ogImage' => $this->seo->ogImage($article)->value,
            'socialTitle' => $this->seo->socialTitle($article)->value,
            'socialDescription' => $this->seo->socialDescription($article)->value,
            'jsonLd' => $this->articleJsonLd($article, $canonical),
        ]);

        if ($article->noindex || $article->nofollow) {
            $directives = array_filter([
                $article->noindex ? 'noindex' : null,
                $article->nofollow ? 'nofollow' : null,
            ]);

            return response($view)->header('X-Robots-Tag', implode(', ', $directives));
        }

        return $view;
    }

    /** @return array<string, mixed> */
    private function indexLayoutData(LengthAwarePaginator $articles, ?ArticleCategory $category): array
    {
        $baseUrl = $category ? $this->categoryUrl($category->slug) : $this->indexUrl();
        $canonical = $articles->currentPage() > 1 ? $baseUrl.'?page='.$articles->currentPage() : $baseUrl;

        $title = $category
            ? $category->meta_title ?: ($category->name.' — مقالات دار الحجامة')
            : 'مقالات عن الحجامة المنزلية في تونس الكبرى | دار الحجامة';

        $description = $category
            ? ($category->meta_description ?: 'مقالات دار الحجامة في تصنيف '.$category->name.'.')
            : 'مقالات ومعلومات عملية حول الحجامة المنزلية في تونس الكبرى: الاستعداد للجلسة، أنواع الحجامة، والأسئلة الشائعة.';

        return [
            'whatsappBookingUrl' => WhatsAppLinkBuilder::make()->link(
                'السلام عليكم، أرغب في حجز موعد حجامة في دار الحجامة.',
            ),
            'seoTitle' => $title,
            'metaDescription' => $description,
            'canonicalUrl' => $canonical,
            'noindex' => false,
            'nofollow' => false,
            'ogType' => 'website',
            'ogImage' => null,
            'socialTitle' => null,
            'socialDescription' => null,
            'jsonLd' => $this->breadcrumbJsonLd(array_filter([
                ['name' => 'الرئيسية', 'url' => route('dar-hijama.public.home')],
                ['name' => 'المقالات', 'url' => $this->indexUrl()],
                $category ? ['name' => $category->name, 'url' => $this->categoryUrl($category->slug)] : null,
            ])),
        ];
    }

    /** @return array<string, mixed> */
    private function articleJsonLd(Article $article, string $canonical): array
    {
        $graph = [
            [
                '@type' => $article->schema_type ?: 'BlogPosting',
                'headline' => $this->seo->seoTitle($article)->value ?? $article->title,
                'description' => $this->seo->metaDescription($article)->value ?? '',
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
                'datePublished' => $article->published_at?->toAtomString(),
                'dateModified' => $article->updated_at?->toAtomString(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $article->author?->name ?? 'دار الحجامة',
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'دار الحجامة',
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/brand/dar-hijama-piste1-icone-512.png'),
                    ],
                ],
            ],
        ];

        $image = $this->seo->ogImage($article)->value;
        if ($image !== null) {
            $graph[0]['image'] = $image;
        }

        $breadcrumb = $this->breadcrumbJsonLd(array_filter([
            ['name' => 'الرئيسية', 'url' => route('dar-hijama.public.home')],
            ['name' => 'المقالات', 'url' => $this->indexUrl()],
            $article->category ? ['name' => $article->category->name, 'url' => $this->categoryUrl($article->category->slug)] : null,
            ['name' => $article->breadcrumb_label ?: $article->title, 'url' => $canonical],
        ]));

        return ['@context' => 'https://schema.org', '@graph' => [$graph[0], $breadcrumb]];
    }

    /** @param array<int, array{name: string, url: string}> $items */
    private function breadcrumbJsonLd(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect(array_values($items))->map(fn (array $item, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ])->all(),
        ];
    }

    private function categoriesWithPublishedArticles(): \Illuminate\Support\Collection
    {
        return ArticleCategory::query()
            ->whereHas('articles', fn ($q) => $q->published())
            ->withCount(['articles' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();
    }

    private function whatsappUrl(Article $article): string
    {
        return WhatsAppLinkBuilder::make()->link(
            'السلام عليكم، قرأت مقال «'.$article->title.'» وأرغب في حجز موعد حجامة.',
        );
    }

    private function indexUrl(): string
    {
        return rtrim((string) config('app.url'), '/').'/articles';
    }

    private function categoryUrl(string $slug): string
    {
        return $this->indexUrl().'/category/'.$slug;
    }

    private function articleUrl(string $slug): string
    {
        return $this->indexUrl().'/'.$slug;
    }
}
