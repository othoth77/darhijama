<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHomePiste15Test extends TestCase
{
    use RefreshDatabase;

    private const HOME = 'http://darhijama.tn/';

    public function test_hero_communicates_service_location_and_booking(): void
    {
        $response = $this->get(self::HOME);

        $response->assertOk()
            ->assertSeeText('الحجامة المنزلية في تونس الكبرى')
            ->assertSeeText('نوصلك إلى منزلك بمواعيد منظمة.')
            ->assertSeeText('احجز موعدك')
            ->assertSeeText('تواصل معنا عبر واتساب');

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_sections_follow_the_booking_first_structure(): void
    {
        $this->get(self::HOME)->assertOk()->assertSeeTextInOrder([
            'الخدمات',
            'المقالات',
            'الأسئلة الشائعة',
            'اتصل بنا',
            'خدمة منزلية',
            'مواعيد منظمة',
            'تونس الكبرى',
            'كيف تتم الخدمة؟',
            'ننسق معك الموعد',
            'نصل إلى منزلك',
            'الحجامة المنزلية',
            'نخدم تونس الكبرى',
            'أسئلة يتكرر طرحها',
            'جاهز لحجز موعدك؟',
        ]);
    }

    public function test_head_seo_and_medical_business_schema_are_preserved(): void
    {
        $response = $this->get(self::HOME);

        $response->assertOk()
            ->assertSee('<title>الحجامة المنزلية في تونس الكبرى | دار الحجامة</title>', false)
            ->assertSee('<link rel="canonical" href="https://darhijama.tn/">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('name="description"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('property="og:image"', false);

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $response->getContent(), $matches);
        $schema = json_decode($matches[1] ?? '', true);

        $this->assertIsArray($schema);
        $this->assertSame('MedicalBusiness', $schema['@type']);
        $this->assertSame(['تونس', 'أريانة', 'بن عروس', 'منوبة'], array_column($schema['areaServed'], 'name'));
    }

    public function test_fonts_load_from_the_host_allowed_by_the_content_security_policy(): void
    {
        foreach ([self::HOME, 'http://darhijama.tn/articles'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('https://fonts.bunny.net/css?family=cairo', false)
                ->assertDontSee('fonts.googleapis.com', false);
        }
    }

    public function test_booking_and_contact_ctas_are_whatsapp_links(): void
    {
        $content = $this->get(self::HOME)->getContent();

        $this->assertGreaterThanOrEqual(6, substr_count($content, 'href="https://wa.me/'));
        $this->assertStringContainsString('dar-hijama-piste1-icone.svg', $content);
    }

    public function test_latest_articles_show_three_indexable_cards_with_crawlable_links(): void
    {
        $category = ArticleCategory::factory()->create(['name' => 'تصنيف تجريبي']);

        foreach (range(1, 4) as $day) {
            Article::factory()->published()->create([
                'title' => "مقال رقم {$day}",
                'slug' => "article-{$day}",
                'excerpt' => "ملخص المقال {$day}",
                'category_id' => $category->id,
                'published_at' => now()->subDays($day + 1),
            ]);
        }

        Article::factory()->published()->create([
            'title' => 'مقال غير مفهرس',
            'noindex' => true,
            'published_at' => now()->subHour(),
        ]);

        $response = $this->get(self::HOME);

        $response->assertOk()
            ->assertSeeText('أحدث المقالات')
            ->assertSeeText('تصنيف تجريبي')
            ->assertSeeText('ملخص المقال 1')
            ->assertSee('href="http://darhijama.tn/articles/article-1"', false)
            ->assertDontSeeText('مقال رقم 4')
            ->assertDontSeeText('مقال غير مفهرس');

        $this->assertSame(3, substr_count($response->getContent(), 'اقرأ المقال'));
    }

    public function test_articles_section_is_omitted_when_nothing_is_published(): void
    {
        $this->get(self::HOME)->assertOk()->assertDontSeeText('أحدث المقالات');
    }

    public function test_article_pages_share_the_new_navigation_and_booking_cta(): void
    {
        $this->get('http://darhijama.tn/articles')
            ->assertOk()
            ->assertSee('href="http://darhijama.tn#faq"', false)
            ->assertSeeText('احجز موعدك')
            ->assertSeeText('دخول فريق العمل');
    }
}
