<?php

namespace Applications\DarHijama\Domain;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ArticleTag extends Model
{
    protected $table = 'dar_hijama_article_tags';

    protected $fillable = ['name', 'slug'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'dar_hijama_article_tag', 'tag_id', 'article_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $tag): void {
            if (blank($tag->slug)) {
                $tag->slug = Str::slug($tag->name) ?: (string) Str::uuid();
            }
        });
    }
}
