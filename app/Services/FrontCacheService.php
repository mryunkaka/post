<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FrontCacheService
{
    public const CATEGORY_ACTIVE_TTL = 3600;

    public const SETTINGS_AUTOLOAD_TTL = 3600;

    public const POPULAR_ARTICLES_TTL = 1800;

    public const HOMEPAGE_TTL = 600;

    public function rememberActiveCategories(): Collection
    {
        $cacheKey = $this->key('categories.active');
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return Category::hydrate($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $payload = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values()
            ->all();

        Cache::put($cacheKey, $payload, self::CATEGORY_ACTIVE_TTL);

        return Category::hydrate($payload);
    }

    /**
     * @return array{headline: ?Article, latestArticles: Collection<int, Article>, popularArticles: Collection<int, Article>, mainCategories: Collection<int, Category>}
     */
    public function rememberHomepagePayload(): array
    {
        $cacheKey = $this->key('homepage.payload');
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return [
                'headline' => $this->hydrateArticle(Arr::get($cached, 'headline')),
                'latestArticles' => $this->hydrateArticles(Arr::get($cached, 'latestArticles', [])),
                'popularArticles' => $this->rememberPopularArticles(),
                'mainCategories' => Category::hydrate(Arr::get($cached, 'mainCategories', [])),
            ];
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $headline = Article::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->first();

        $latestArticles = Article::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->when($headline, fn ($query) => $query->whereKeyNot($headline->id))
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        $mainCategories = Category::query()
            ->active()
            ->withCount([
                'articles as published_articles_count' => fn ($query) => $query->publiclyVisible(),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(8)
            ->get();

        $payload = [
            'headline' => $headline ? $this->serializeArticle($headline) : null,
            'latestArticles' => $latestArticles->map(fn (Article $article) => $this->serializeArticle($article))->all(),
            'mainCategories' => $mainCategories->map(fn (Category $category) => $category->attributesToArray())->all(),
        ];

        Cache::put($cacheKey, $payload, self::HOMEPAGE_TTL);

        return [
            'headline' => $headline,
            'latestArticles' => $latestArticles,
            'popularArticles' => $this->rememberPopularArticles(),
            'mainCategories' => $mainCategories,
        ];
    }

    /**
     * @return Collection<int, Article>
     */
    public function rememberPopularArticles(): Collection
    {
        $cacheKey = $this->key('articles.popular');
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return $this->hydrateArticles($cached);
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        $payload = Article::query()
            ->publiclyVisible()
            ->with(['category:id,name,slug'])
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->take(5)
            ->get()
            ->map(fn (Article $article) => $this->serializeArticle($article))
            ->all();

        Cache::put($cacheKey, $payload, self::POPULAR_ARTICLES_TTL);

        return $this->hydrateArticles($payload);
    }

    public function forgetHomepage(): void
    {
        Cache::forget($this->key('homepage.payload'));
    }

    public function forgetPopularArticles(): void
    {
        Cache::forget($this->key('articles.popular'));
    }

    public function forgetActiveCategories(): void
    {
        Cache::forget($this->key('categories.active'));
    }

    public function forgetSettingsAutoload(): void
    {
        Cache::forget($this->key('settings.autoload'));
    }

    public function flushArticleRelatedCaches(): void
    {
        $this->forgetHomepage();
        $this->forgetPopularArticles();
    }

    public function flushCategoryRelatedCaches(): void
    {
        $this->forgetActiveCategories();
        $this->forgetHomepage();
    }

    public function flushSettingRelatedCaches(): void
    {
        $this->forgetSettingsAutoload();
    }

    public function key(string $resource): string
    {
        return config('app.name').':cache:'.$resource;
    }

    protected function serializeArticle(Article $article): array
    {
        return [
            'attributes' => $article->attributesToArray(),
            'author' => $article->relationLoaded('author') && $article->author !== null
                ? $article->author->attributesToArray()
                : null,
            'category' => $article->relationLoaded('category') && $article->category !== null
                ? $article->category->attributesToArray()
                : null,
            'tags' => $article->relationLoaded('tags')
                ? $article->tags->map(fn (Tag $tag) => $tag->attributesToArray())->all()
                : [],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return Collection<int, Article>
     */
    protected function hydrateArticles(array $rows): Collection
    {
        return collect($rows)
            ->map(fn (array $row) => $this->hydrateArticle($row))
            ->filter()
            ->values();
    }

    /**
     * @param  array<string, mixed>|null  $row
     */
    protected function hydrateArticle(?array $row): ?Article
    {
        if ($row === null) {
            return null;
        }

        $article = new Article;
        $article->forceFill(Arr::get($row, 'attributes', []));
        $article->exists = true;

        $author = Arr::get($row, 'author');

        if (is_array($author)) {
            $authorModel = new User;
            $authorModel->forceFill($author);
            $authorModel->exists = true;
            $article->setRelation('author', $authorModel);
        }

        $category = Arr::get($row, 'category');

        if (is_array($category)) {
            $categoryModel = new Category;
            $categoryModel->forceFill($category);
            $categoryModel->exists = true;
            $article->setRelation('category', $categoryModel);
        }

        $tags = Arr::get($row, 'tags', []);

        if (is_array($tags)) {
            $article->setRelation(
                'tags',
                new EloquentCollection(
                    collect($tags)
                        ->map(function (array $tag): Tag {
                            $tagModel = new Tag;
                            $tagModel->forceFill($tag);
                            $tagModel->exists = true;

                            return $tagModel;
                        })
                        ->all()
                )
            );
        }

        return $article;
    }
}
