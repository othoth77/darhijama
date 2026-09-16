<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Application\Services\Seo\ArticleSeoResolver;
use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleCategory;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mythos\Core\Identity\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_article_is_not_publicly_visible(): void
    {
        $article = Article::factory()->create(['slug' => 'draft-article']);

        $this->get('http://darhijama.tn/articles/draft-article')->assertNotFound();
    }

    public function test_scheduled_article_is_not_publicly_visible(): void
    {
        $article = Article::factory()->scheduled()->create(['slug' => 'scheduled-article']);

        $this->get('http://darhijama.tn/articles/scheduled-article')->assertNotFound();
    }

    public function test_published_article_is_publicly_visible(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'published-article', 'title' => 'مقال منشور']);

        $this->get('http://darhijama.tn/articles/published-article')
            ->assertOk()
            ->assertSee('مقال منشور')
            ->assertSee('BreadcrumbList', false)
            ->assertSee('BlogPosting', false);
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('http://darhijama.tn/articles/does-not-exist')->assertNotFound();
    }

    public function test_slug_change_creates_a_301_redirect_from_the_old_slug(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'old-slug']);
        $article->update(['slug' => 'new-slug']);

        $this->assertDatabaseHas('dar_hijama_article_redirects', [
            'old_slug' => 'old-slug',
            'article_id' => $article->id,
        ]);

        $this->get('http://darhijama.tn/articles/old-slug')
            ->assertRedirect('http://darhijama.tn/articles/new-slug')
            ->assertStatus(301);

        $this->get('http://darhijama.tn/articles/new-slug')->assertOk();
    }

    public function test_slug_must_be_unique(): void
    {
        Article::factory()->create(['slug' => 'duplicate-slug']);

        $this->expectException(QueryException::class);
        Article::factory()->create(['slug' => 'duplicate-slug']);
    }

    public function test_rich_content_is_sanitized_before_rendering(): void
    {
        $article = Article::factory()->published()->create([
            'slug' => 'xss-test',
            'content' => '<p>مرحبا</p><script>alert(1)</script><img src=x onerror="alert(2)">',
        ]);

        $response = $this->get('http://darhijama.tn/articles/xss-test');

        $response->assertOk();
        // The page legitimately has its own JSON-LD <script> tag; what must
        // never survive is the injected payload itself.
        $response->assertDontSee('alert(1)', false);
        $response->assertDontSee('alert(2)', false);
        $response->assertDontSee('onerror', false);
        $response->assertSee('مرحبا');
    }

    public function test_sitemap_includes_only_published_articles(): void
    {
        Article::factory()->published()->create(['slug' => 'in-sitemap']);
        Article::factory()->create(['slug' => 'not-in-sitemap-draft']);
        Article::factory()->scheduled()->create(['slug' => 'not-in-sitemap-scheduled']);

        $response = $this->get('http://darhijama.tn/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('https://darhijama.tn/articles/in-sitemap', false);
        $response->assertDontSee('not-in-sitemap-draft', false);
        $response->assertDontSee('not-in-sitemap-scheduled', false);
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
    }

    public function test_sitemap_excludes_admin_and_only_this_apps_urls(): void
    {
        $response = $this->get('http://darhijama.tn/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee('/admin', false);
    }

    public function test_robots_txt_disallows_admin_and_declares_sitemap(): void
    {
        $response = $this->get('http://darhijama.tn/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Sitemap: https://darhijama.tn/sitemap.xml', false);
        $response->assertDontSee('Disallow: /articles', false);
    }

    public function test_seo_title_falls_back_to_title_and_site_suffix_when_empty(): void
    {
        $article = Article::factory()->make(['title' => 'عنوان تجريبي', 'seo_title' => null]);

        $resolved = app(ArticleSeoResolver::class)->seoTitle($article);

        $this->assertTrue($resolved->isAuto);
        $this->assertSame('عنوان تجريبي | دار الحجامة', $resolved->value);
    }

    public function test_seo_title_uses_manual_value_when_provided(): void
    {
        $article = Article::factory()->make(['title' => 'عنوان', 'seo_title' => 'عنوان SEO مخصص']);

        $resolved = app(ArticleSeoResolver::class)->seoTitle($article);

        $this->assertFalse($resolved->isAuto);
        $this->assertSame('عنوان SEO مخصص', $resolved->value);
    }

    public function test_meta_description_falls_back_to_excerpt(): void
    {
        $article = Article::factory()->make(['excerpt' => 'ملخص قصير للمقال', 'meta_description' => null]);

        $resolved = app(ArticleSeoResolver::class)->metaDescription($article);

        $this->assertTrue($resolved->isAuto);
        $this->assertSame('ملخص قصير للمقال', $resolved->value);
    }

    public function test_canonical_falls_back_to_article_route(): void
    {
        $article = Article::factory()->make(['slug' => 'my-article', 'canonical_url' => null]);

        $resolved = app(ArticleSeoResolver::class)->canonicalUrl($article);

        $this->assertTrue($resolved->isAuto);
        $this->assertStringEndsWith('/articles/my-article', $resolved->value);
    }

    public function test_noindex_article_sends_x_robots_tag_header(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'noindex-article', 'noindex' => true]);

        $response = $this->get('http://darhijama.tn/articles/noindex-article');

        $response->assertOk();
        $response->assertHeader('X-Robots-Tag', 'noindex');
        $response->assertSee('name="robots" content="noindex, follow"', false);
    }

    public function test_articles_index_lists_only_published_articles(): void
    {
        Article::factory()->published()->create(['title' => 'مقال ظاهر']);
        Article::factory()->create(['title' => 'مقال مخفي']);

        $this->get('http://darhijama.tn/articles')
            ->assertOk()
            ->assertSee('مقال ظاهر')
            ->assertDontSee('مقال مخفي');
    }

    public function test_category_page_scopes_articles_to_that_category(): void
    {
        $categoryA = ArticleCategory::factory()->create(['name' => 'تصنيف أ']);
        $categoryB = ArticleCategory::factory()->create(['name' => 'تصنيف ب']);
        Article::factory()->published()->create(['title' => 'مقال أ', 'category_id' => $categoryA->id]);
        Article::factory()->published()->create(['title' => 'مقال ب', 'category_id' => $categoryB->id]);

        $this->get('http://darhijama.tn/articles/category/'.$categoryA->slug)
            ->assertOk()
            ->assertSee('مقال أ')
            ->assertDontSee('مقال ب');
    }

    public function test_admin_can_reach_the_articles_list_page(): void
    {
        $this->artisan('dar-hijama:install')->assertSuccessful();
        $admin = User::factory()->create();
        Role::findOrCreate('admin');
        $admin->assignRole(['dar-hijama-admin', 'admin']);

        $this->actingAs($admin)
            ->get('http://darhijama.tn/admin/articles')
            ->assertOk()
            ->assertSee('المقالات');
    }

    public function test_admin_can_reach_the_article_create_page(): void
    {
        $this->artisan('dar-hijama:install')->assertSuccessful();
        $admin = User::factory()->create();
        Role::findOrCreate('admin');
        $admin->assignRole(['dar-hijama-admin', 'admin']);

        $this->actingAs($admin)
            ->get('http://darhijama.tn/admin/articles/create')
            ->assertOk();
    }

    public function test_guest_cannot_reach_the_admin_articles_page(): void
    {
        $this->get('http://darhijama.tn/admin/articles')->assertRedirect();
    }

    public function test_user_without_the_articles_permission_is_forbidden(): void
    {
        $this->artisan('dar-hijama:install')->assertSuccessful();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('http://darhijama.tn/admin/articles')
            ->assertForbidden();
    }

    public function test_homepage_links_to_the_articles_index(): void
    {
        Article::factory()->published()->create();

        $this->get('http://darhijama.tn/')
            ->assertOk()
            ->assertSee('darhijama.tn/articles"', false);
    }
}
