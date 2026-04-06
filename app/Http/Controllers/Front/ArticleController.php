<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleViewBufferService;
use App\Services\CommentService;
use App\Services\FrontCacheService;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class ArticleController extends Controller
{
    public function __construct(
        protected FrontCacheService $frontCacheService,
        protected ArticleViewBufferService $articleViewBufferService,
        protected CommentService $commentService,
    ) {}

    public function show(string $articleSlug)
    {
        $article = Article::query()
            ->with([
                'author:id,name',
                'category:id,name,slug',
                'tags:id,name,slug',
                'comments' => fn ($query) => $query
                    ->approved()
                    ->rootLevel()
                    ->with([
                        'author:id,name',
                        'replies' => fn ($replyQuery) => $replyQuery
                            ->approved()
                            ->with('author:id,name')
                            ->oldest(),
                    ])
                    ->oldest(),
            ])
            ->where('slug', $articleSlug)
            ->firstOrFail();

        if ($article->archived_at !== null) {
            throw new GoneHttpException('Artikel ini telah diarsipkan.');
        }

        abort_unless(
            $article->status === 'published'
                && $article->published_at !== null
                && $article->published_at->lte(now()),
            404
        );

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
            'commentsEnabled' => $this->commentService->commentsEnabled(),
            'approvedComments' => $article->comments,
            'metaTitle' => $article->meta_title ?: $article->title,
            'metaDescription' => $article->meta_description ?: $article->excerpt,
        ]);
    }
}
