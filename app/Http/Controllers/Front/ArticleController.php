<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleController extends Controller
{
    public function show(string $articleSlug)
    {
        $article = Article::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->where('slug', $articleSlug)
            ->firstOrFail();

        $relatedArticles = Article::query()
            ->publiclyVisible()
            ->with(['category:id,name,slug'])
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->id)
            ->orderByDesc('published_at')
            ->take(4)
            ->get();

        $popularArticles = Article::query()
            ->publiclyVisible()
            ->with(['category:id,name,slug'])
            ->whereKeyNot($article->id)
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        return view('frontend.articles.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'popularArticles' => $popularArticles,
            'metaTitle' => $article->meta_title ?: $article->title,
            'metaDescription' => $article->meta_description ?: $article->excerpt,
        ]);
    }
}
