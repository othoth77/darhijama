<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Application\Services\Seo\ArticleSeoResolver;
use Applications\DarHijama\Domain\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleOgImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_image_produces_a_public_storage_url(): void
    {
        $article = Article::factory()->make(['featured_image' => 'dar-hijama/articles/photo.webp', 'og_image' => null]);

        $resolved = app(ArticleSeoResolver::class)->ogImage($article);

        $this->assertTrue($resolved->isAuto);
        $this->assertSame(Storage::disk('public')->url('dar-hijama/articles/photo.webp'), $resolved->value);
        $this->assertStringContainsString('/storage/dar-hijama/articles/photo.webp', $resolved->value);
    }

    public function test_explicit_og_image_produces_a_public_storage_url(): void
    {
        $article = Article::factory()->make(['og_image' => 'dar-hijama/articles/og/social.webp']);

        $resolved = app(ArticleSeoResolver::class)->ogImage($article);

        $this->assertFalse($resolved->isAuto);
        $this->assertStringContainsString('/storage/dar-hijama/articles/og/social.webp', $resolved->value);
    }

    public function test_an_absolute_og_image_url_is_left_untouched(): void
    {
        $article = Article::factory()->make(['og_image' => 'https://cdn.example.test/social.png']);

        $this->assertSame('https://cdn.example.test/social.png', app(ArticleSeoResolver::class)->ogImage($article)->value);
    }

    public function test_article_page_and_structured_data_expose_the_storage_url(): void
    {
        $expected = Storage::disk('public')->url('dar-hijama/articles/photo.webp');

        Article::factory()->published()->create([
            'slug' => 'og-image-article',
            'featured_image' => 'dar-hijama/articles/photo.webp',
            'featured_image_alt' => 'صورة',
        ]);

        $content = $this->get('http://darhijama.tn/articles/og-image-article')->assertOk()->getContent();

        preg_match('#property="og:image" content="(.*?)"#', $content, $og);
        $this->assertSame($expected, $og[1] ?? null);

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $content, $ld);
        $graph = json_decode($ld[1] ?? '', true)['@graph'] ?? [];
        $posting = collect($graph)->firstWhere('@type', 'BlogPosting');
        $image = is_array($posting['image'] ?? null) ? ($posting['image'][0] ?? null) : ($posting['image'] ?? null);

        $this->assertSame($expected, $image);
    }
}
