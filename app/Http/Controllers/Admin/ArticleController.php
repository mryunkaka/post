<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ArticleController extends Controller
{
    public function __construct(
        protected ArticleService $articleService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $articles = Article::query()
            ->with(['author', 'category', 'tags'])
            ->when($user->role === 'wartawan', fn ($query) => $query->where('user_id', $user->id))
            ->latest()
            ->paginate(12);

        return view('admin.articles.index', [
            'articles' => $articles,
        ]);
    }

    public function create(): View
    {
        return view('admin.articles.create', [
            'article' => new Article([
                'schema_type' => 'NewsArticle',
            ]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = $this->articleService->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel draft berhasil dibuat.');
    }

    public function show(Request $request, Article $article): View
    {
        $this->articleService->assertCanView($request->user(), $article);

        return view('admin.articles.show', [
            'article' => $article->load(['author', 'category', 'tags']),
        ]);
    }

    public function edit(Request $request, Article $article): View
    {
        $this->articleService->assertCanView($request->user(), $article);

        return view('admin.articles.edit', [
            'article' => $article->load(['author', 'category', 'tags']),
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $article = $this->articleService->update($request->user(), $article, $request->validated());

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel berhasil diperbarui.');
    }

    public function submitForReview(Request $request, Article $article): RedirectResponse
    {
        $this->articleService->submitForReview($request->user(), $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel berhasil diajukan ke review.');
    }

    public function publish(Request $request, Article $article): RedirectResponse
    {
        $this->articleService->publish($request->user(), $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel berhasil dipublish.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        $this->articleService->delete($request->user(), $article);

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil dihapus.');
    }

    /**
     * @return Collection<int, Category>
     */
    protected function categories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
