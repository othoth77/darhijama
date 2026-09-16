<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Domain\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleFeaturedImageResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_featured_image_is_bounded_by_its_container_on_small_screens(): void
    {
        Article::factory()->published()->create([
            'slug' => 'responsive-image-article',
            'featured_image' => 'dar-hijama/articles/example.webp',
            'featured_image_alt' => 'صورة المقال',
        ]);

        $content = $this->get('http://darhijama.tn/articles/responsive-image-article')->assertOk()->getContent();

        preg_match('#<img[^>]*dar-hijama/articles/example\.webp[^>]*>#s', $content, $matches);
        $this->assertNotEmpty($matches, 'featured image not rendered');

        $classes = preg_match('#class="([^"]*)"#', $matches[0], $class) ? explode(' ', $class[1]) : [];
        $this->assertContains('w-full', $classes);
        $this->assertContains('max-w-3xl', $classes);
        $this->assertStringContainsString('width="768" height="420"', $matches[0]);
        $this->assertStringContainsString('alt="صورة المقال"', $matches[0]);
    }
}
