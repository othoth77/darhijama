<?php

namespace Database\Factories;

use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 100000),
            'excerpt' => fake()->sentence(15),
            'content' => '<p>'.fake()->paragraph().'</p><h2>'.fake()->sentence(4).'</h2><p>'.fake()->paragraph().'</p>',
            'status' => ArticleStatus::Draft,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Scheduled,
            'scheduled_at' => now()->addDay(),
            'published_at' => null,
        ]);
    }
}
