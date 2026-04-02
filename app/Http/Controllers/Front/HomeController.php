<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class HomeController extends Controller
{
    public function __invoke()
    {
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

        $popularArticles = Article::query()
            ->publiclyVisible()
            ->with(['category:id,name,slug'])
            ->when($headline, fn ($query) => $query->whereKeyNot($headline->id))
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->take(5)
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

        return view('frontend.home', [
            'headline' => $headline,
            'latestArticles' => $latestArticles,
            'popularArticles' => $popularArticles,
            'mainCategories' => $mainCategories,
            'metaTitle' => null,
            'metaDescription' => null,
        ]);
    }
}
