<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;

class CategoryController extends Controller
{
    public function show(string $categorySlug)
    {
        $category = Category::query()
            ->active()
            ->where('slug', $categorySlug)
            ->firstOrFail();

        $articles = Article::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        $popularArticles = Article::query()
            ->publiclyVisible()
            ->with(['category:id,name,slug'])
            ->where('category_id', $category->id)
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        return view('frontend.categories.show', [
            'category' => $category,
            'articles' => $articles,
            'popularArticles' => $popularArticles,
            'metaTitle' => $category->seo_title ?: $category->name,
            'metaDescription' => $category->seo_description ?: $category->description,
        ]);
    }
}
