<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
    ) {}

    public function index(Request $request): View
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $comments = $this->filteredQuery($request)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'currentStatus' => $status,
            'searchQuery' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->commentService->approve($comment);

        return back()->with('status', 'Komentar berhasil disetujui.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $this->commentService->reject($comment);

        return back()->with('status', 'Komentar berhasil ditolak.');
    }

    public function spam(Comment $comment): RedirectResponse
    {
        $this->commentService->markAsSpam($comment);

        return back()->with('status', 'Komentar ditandai sebagai spam.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->commentService->delete($comment);

        return back()->with('status', 'Komentar berhasil dihapus.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject,spam,delete'],
            'selection_scope' => ['nullable', 'string', 'in:page,filtered,all'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer'],
            'status' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $comments = $this->resolveBulkSelection($request, $validated['selection_scope'] ?? 'page');

        if ($comments->isEmpty()) {
            return back()->with('status', 'Tidak ada komentar yang dipilih.');
        }

        foreach ($comments as $comment) {
            match ($validated['action']) {
                'approve' => $this->commentService->approve($comment),
                'reject' => $this->commentService->reject($comment),
                'spam' => $this->commentService->markAsSpam($comment),
                'delete' => $this->commentService->delete($comment),
            };
        }

        return redirect()
            ->route('admin.comments.index', $this->bulkQueryString($validated))
            ->with('status', 'Bulk action berhasil diproses untuk '.$comments->count().' komentar.');
    }

    protected function baseQuery(): Builder
    {
        return Comment::query()
            ->with(['article:id,title,slug', 'parent:id,content', 'author:id,name,email']);
    }

    protected function filteredQuery(Request $request): Builder
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        return $this->baseQuery()
            ->when(in_array($status, ['pending', 'approved', 'rejected', 'spam'], true), fn (Builder $query) => $query->where('status', $status))
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $like = '%'.$search.'%';

                    $searchQuery
                        ->where('guest_name', 'like', $like)
                        ->orWhere('guest_email', 'like', $like)
                        ->orWhere('content', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhereHas('author', fn (Builder $authorQuery) => $authorQuery->where('name', 'like', $like)->orWhere('email', 'like', $like))
                        ->orWhereHas('article', fn (Builder $articleQuery) => $articleQuery->where('title', 'like', $like));
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

    protected function resolveBulkSelection(Request $request, string $scope)
    {
        if ($scope === 'all') {
            return $this->baseQuery()->get();
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

        return $this->baseQuery()->whereKey($ids)->get();
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
}
