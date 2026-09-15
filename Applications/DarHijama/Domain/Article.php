<?php

namespace Applications\DarHijama\Domain;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Mews\Purifier\Facades\Purifier;
use Mythos\Core\Identity\Models\User;

/**
 * @use HasFactory<ArticleFactory>
 */
class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dar_hijama_articles';

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_alt',
        'author_id',
        'category_id',
        'status',
        'published_at',
        'scheduled_at',
        'seo_title',
        'meta_description',
        'focus_keyword',
        'secondary_keywords',
        'canonical_url',
        'noindex',
        'nofollow',
        'schema_type',
        'breadcrumb_label',
        'og_image',
        'social_title',
        'social_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'noindex' => 'boolean',
            'nofollow' => 'boolean',
        ];
    }

    protected static function newFactory(): ArticleFactory
    {
        return ArticleFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ArticleTag::class, 'dar_hijama_article_tag', 'article_id', 'tag_id');
    }

    public function redirects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ArticleRedirect::class);
    }

    /**
     * The only visibility rule for public traffic: published status AND a
     * published_at timestamp that has actually arrived. A "scheduled"
     * article whose time has passed but whose status was never flipped by
     * the scheduler is intentionally still excluded — status is authoritative.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    /**
     * The rich-editor's stored HTML is admin-authored, but still passed
     * through HTMLPurifier before any public rendering — see
     * config/purifier.php's "dar_hijama_article" profile, kept in sync with
     * the RichEditor toolbar in ArticleResource. Never echo $article->content
     * raw in a Blade view; always call this instead.
     */
    public function purifiedContent(): string
    {
        return Purifier::clean((string) $this->content, 'dar_hijama_article');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === ArticleStatus::Published
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(Carbon::now());
    }

    protected static function booted(): void
    {
        static::updating(function (self $article): void {
            if ($article->isDirty('slug') && $article->getOriginal('slug') !== null) {
                ArticleRedirect::updateOrCreate(
                    ['old_slug' => $article->getOriginal('slug')],
                    ['article_id' => $article->id],
                );
            }
        });
    }
}
