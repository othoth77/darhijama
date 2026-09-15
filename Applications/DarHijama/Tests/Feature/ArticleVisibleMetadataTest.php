<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Domain\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mythos\Core\Identity\Models\User;
use Tests\TestCase;

class ArticleVisibleMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_page_hides_author_and_dates_but_keeps_structured_data(): void
    {
        $author = User::factory()->create(['name' => 'Othman Haddad']);
        $article = Article::factory()->published()->create([
            'slug' => 'hidden-meta-article',
            'author_id' => $author->id,
            'published_at' => now()->subDays(3),
        ]);
        $article->forceFill(['updated_at' => now()])->saveQuietly();

        $response = $this->get('http://darhijama.tn/articles/hidden-meta-article');

        $response->assertOk()
            ->assertDontSeeText('نُشر في')
            ->assertDontSeeText('آخر تحديث')
            ->assertDontSee('<time', false);

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);
        $posting = collect(json_decode($matches[1] ?? '', true)['@graph'] ?? [])->firstWhere('@type', 'BlogPosting');

        $this->assertNotNull($posting);
        $this->assertSame($article->fresh()->published_at->toAtomString(), $posting['datePublished']);
        $this->assertSame($article->fresh()->updated_at->toAtomString(), $posting['dateModified']);
        $this->assertSame('Othman Haddad', $posting['author']['name']);
    }

    public function test_sitemap_keeps_lastmod_for_articles(): void
    {
        Article::factory()->published()->create(['slug' => 'lastmod-article']);

        $this->get('http://darhijama.tn/sitemap.xml')
            ->assertOk()
            ->assertSee('https://darhijama.tn/articles/lastmod-article', false)
            ->assertSee('<lastmod>', false);
    }
}
