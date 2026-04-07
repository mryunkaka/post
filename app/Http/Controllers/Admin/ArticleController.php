<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
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
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $articles = $this->filteredQuery($request)
            ->latest('updated_at')
            ->paginate(12);

        return view('admin.articles.index', [
            'articles' => $articles,
            'currentStatus' => $status,
            'searchQuery' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statusOptions' => ['draft', 'review', 'published'],
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
        $article = $this->articleService->publish($request->user(), $article);

        $message = $article->status === 'published'
            ? 'Artikel berhasil dipublish.'
            : 'Artikel berhasil dijadwalkan publish.';

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', $message);
    }

    public function archive(Request $request, Article $article): RedirectResponse
    {
        $this->articleService->archive($request->user(), $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel berhasil diarsipkan.');
    }

    public function restore(Request $request, Article $article): RedirectResponse
    {
        $this->articleService->restore($request->user(), $article);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Artikel arsip berhasil dipulihkan.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        $this->articleService->delete($request->user(), $article);

        return redirect()
            ->route('admin.articles.index')
            ->with('status', 'Artikel berhasil dihapus.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:delete,submit_review,publish,archive,restore'],
            'selection_scope' => ['nullable', 'string', 'in:page,filtered,all'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer'],
            'status' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $articles = $this->resolveBulkSelection($request, $validated['selection_scope'] ?? 'page');

        if ($articles->isEmpty()) {
            return back()->with('status', 'Tidak ada artikel yang dipilih.');
        }

        $processed = 0;
        $failed = 0;
        $actor = $request->user();

        foreach ($articles as $article) {
            try {
                match ($validated['action']) {
                    'delete' => $this->articleService->delete($actor, $article),
                    'submit_review' => $this->articleService->submitForReview($actor, $article),
                    'publish' => $this->articleService->publish($actor, $article),
                    'archive' => $this->articleService->archive($actor, $article),
                    'restore' => $this->articleService->restore($actor, $article),
                };
                $processed++;
            } catch (AuthorizationException) {
                $failed++;
            }
        }

        return redirect()
            ->route('admin.articles.index', $this->bulkQueryString($validated))
            ->with('status', $this->buildBulkStatusMessage('artikel', $processed, $failed));
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

    protected function baseQuery(Request $request): Builder
    {
        $user = $request->user();

        return Article::query()
            ->with(['author', 'category', 'tags'])
            ->when($user->role === 'wartawan', fn (Builder $query) => $query->where('user_id', $user->id));
    }

    protected function filteredQuery(Request $request): Builder
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        return $this->baseQuery($request)
            ->when(in_array($status, ['draft', 'review', 'published'], true), fn (Builder $query) => $query->where('status', $status))
            ->whereDate('updated_at', '>=', $dateFrom)
            ->whereDate('updated_at', '<=', $dateTo)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $like = '%'.$search.'%';

                    $searchQuery
                        ->where('title', 'like', $like)
                        ->orWhere('slug', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('source_name', 'like', $like)
                        ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', $like))
                        ->orWhereHas('tags', fn (Builder $tagQuery) => $tagQuery->where('name', 'like', $like));
                });
            });
    }

    protected function resolveDateRange(Request $request): array
    {
        $today = now()->toDateString();
        $dateFrom = $request->string('date_from')->toString() ?: $today;
        $dateTo = $request->string('date_to')->toString() ?: $today;

        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [$dateFrom, $dateTo];
    }

    protected function resolveBulkSelection(Request $request, string $scope): Collection
    {
        if ($scope === 'all') {
            return $this->baseQuery($request)->get();
        }

        if ($scope === 'filtered') {
            return $this->filteredQuery($request)->get();
        }

        $ids = collect($request->input('selected_ids', []))
            ->map(fn (mixed $id) => (int) $id)
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->baseQuery($request)
            ->whereKey($ids)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function bulkQueryString(array $validated): array
    {
        return array_filter([
            'status' => $validated['status'] ?? null,
            'q' => $validated['q'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ], fn (mixed $value) => $value !== null && $value !== '');
    }

    protected function buildBulkStatusMessage(string $label, int $processed, int $failed): string
    {
        if ($processed === 0 && $failed > 0) {
            return 'Bulk action gagal diproses untuk '.$failed.' '.$label.'.';
        }

        if ($failed === 0) {
            return 'Bulk action berhasil diproses untuk '.$processed.' '.$label.'.';
        }

        return 'Bulk action selesai: '.$processed.' '.$label.' berhasil, '.$failed.' gagal.';
    }
}
