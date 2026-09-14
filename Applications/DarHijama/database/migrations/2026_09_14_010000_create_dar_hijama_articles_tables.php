<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dar_hijama_article_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('dar_hijama_article_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 60);
            $table->string('slug', 80)->unique();
            $table->timestamps();
        });

        Schema::create('dar_hijama_articles', function (Blueprint $table): void {
            $table->id();

            // Core content
            $table->string('title', 200);
            $table->string('slug', 220)->unique();
            $table->string('excerpt', 320)->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt', 160)->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('category_id')->nullable()
                ->constrained('dar_hijama_article_categories')->nullOnDelete();

            // Publishing workflow
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();

            // SEO — basic
            $table->string('seo_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('focus_keyword', 120)->nullable();
            $table->string('secondary_keywords', 320)->nullable();
            $table->string('canonical_url')->nullable();

            // SEO — advanced
            $table->boolean('noindex')->default(false);
            $table->boolean('nofollow')->default(false);
            $table->string('schema_type', 30)->default('BlogPosting');
            $table->string('breadcrumb_label', 100)->nullable();

            // Social
            $table->string('og_image')->nullable();
            $table->string('social_title')->nullable();
            $table->string('social_description', 320)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('dar_hijama_article_tag', function (Blueprint $table): void {
            $table->foreignId('article_id')->constrained('dar_hijama_articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('dar_hijama_article_tags')->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });

        // Keeps old article URLs alive as 301s after a slug changes — see
        // Article::updating() in the Article model, which writes here
        // automatically instead of ever allowing a broken link.
        Schema::create('dar_hijama_article_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('old_slug', 220)->unique();
            $table->foreignId('article_id')->constrained('dar_hijama_articles')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dar_hijama_article_redirects');
        Schema::dropIfExists('dar_hijama_article_tag');
        Schema::dropIfExists('dar_hijama_articles');
        Schema::dropIfExists('dar_hijama_article_tags');
        Schema::dropIfExists('dar_hijama_article_categories');
    }
};
