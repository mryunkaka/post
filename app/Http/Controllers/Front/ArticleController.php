<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleViewBufferService;
use App\Services\FrontCacheService;

class ArticleController extends Controller
{
    public function __construct(
        protected FrontCacheService $frontCacheService,
        protected ArticleViewBufferService $articleViewBufferService,
    ) {}

    public function show(string $articleSlug)
    {
        $article = Article::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->where('slug', $articleSlug)
            ->firstOrFail();

        $this->articleViewBufferService->record($article);

        $relatedArticles = Article::query()
            ->publiclyVisible()
            ->with(['category:id,name,slug'])
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->id)
            ->orderByDesc('published_at')
            ->take(4)
            ->get();

        $popularArticles = $this->frontCacheService
            ->rememberPopularArticles()
            ->reject(fn ($popularArticle) => $popularArticle->id === $article->id)
            ->take(5)
            ->values();

        return view('frontend.articles.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'popularArticles' => $popularArticles,
            'metaTitle' => $article->meta_title ?: $article->title,
            'metaDescription' => $article->meta_description ?: $article->excerpt,
        ]);
    }
}
