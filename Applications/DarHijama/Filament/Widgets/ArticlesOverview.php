<?php

namespace Applications\DarHijama\Filament\Widgets;

use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArticlesOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'المقالات';

    protected function getStats(): array
    {
        return [
            Stat::make('مسودات', Article::query()->where('status', ArticleStatus::Draft)->count()),
            Stat::make('مجدولة', Article::query()->where('status', ArticleStatus::Scheduled)->count()),
            Stat::make('منشورة', Article::query()->where('status', ArticleStatus::Published)->count()),
            Stat::make(
                'تحتاج SEO',
                Article::query()
                    ->whereIn('status', [ArticleStatus::Published, ArticleStatus::Scheduled])
                    ->where(fn ($q) => $q->whereNull('seo_title')->orWhereNull('meta_description'))
                    ->count(),
            ),
            Stat::make(
                'بدون صورة رئيسية',
                Article::query()
                    ->whereIn('status', [ArticleStatus::Published, ArticleStatus::Scheduled])
                    ->whereNull('featured_image')
                    ->count(),
            ),
        ];
    }
}
