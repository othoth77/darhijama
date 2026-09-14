<?php

namespace Applications\DarHijama\Domain;

use Database\Factories\ArticleCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ArticleCategory extends Model
{
    use HasFactory;

    protected $table = 'dar_hijama_article_categories';

    protected static function newFactory(): ArticleCategoryFactory
    {
        return ArticleCategoryFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'description',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function publishedArticles(): HasMany
    {
        return $this->articles()->published();
    }

    protected static function booted(): void
    {
        static::creating(function (self $category): void {
            if (blank($category->slug)) {
                // Laravel's Str::slug() transliterates Arabic natively (tested:
                // "الحجامة المنزلية" -> "alhgam-almnzly") — kept deliberately
                // over a raw-Arabic slug for URL/social-share stability; see
                // docs/SEO_CONTENT_STRATEGY.md "Slug strategy".
                $category->slug = Str::slug($category->name) ?: (string) Str::uuid();
            }
        });
    }
}
