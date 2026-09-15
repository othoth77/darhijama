<?php

namespace Applications\DarHijama\Policies;

use Applications\DarHijama\Domain\Article;
use Mythos\Core\Identity\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dar-hijama.articles.manage');
    }

    public function view(User $user, Article $article): bool
    {
        return $user->can('dar-hijama.articles.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('dar-hijama.articles.manage');
    }

    public function update(User $user, Article $article): bool
    {
        return $user->can('dar-hijama.articles.manage');
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->can('dar-hijama.articles.manage');
    }
}
