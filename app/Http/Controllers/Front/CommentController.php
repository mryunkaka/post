<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\StoreCommentRequest;
use App\Models\Article;
use App\Services\CommentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
    ) {}

    public function store(StoreCommentRequest $request, string $articleSlug): RedirectResponse
    {
        $article = Article::query()
            ->where('slug', $articleSlug)
            ->firstOrFail();

        try {
            $comment = $this->commentService->createGuestComment($article, $request->validated(), $request);
        } catch (AuthorizationException $exception) {
            return redirect()
                ->route('articles.show', $article->slug)
                ->withErrors(['content' => $exception->getMessage()]);
        }

        return redirect()
            ->route('articles.show', $article->slug)
            ->with('comment_status', $comment
                ? 'Komentar Anda sudah dikirim dan sedang menunggu moderasi.'
                : 'Komentar Anda sudah diterima dan sedang menunggu moderasi.');
    }
}
