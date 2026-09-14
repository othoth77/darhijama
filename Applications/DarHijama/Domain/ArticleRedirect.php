<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A 301 breadcrumb: every slug an Article has ever had, mapped to the
 * article's current slug, so a changed slug never produces a broken link.
 * Written automatically by Article::updating() — never created by hand.
 */
class ArticleRedirect extends Model
{
    protected $table = 'dar_hijama_article_redirects';

    protected $fillable = ['old_slug', 'article_id'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
