<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCandidate;
use App\Services\NewsCandidateValidationService;
use App\Services\NewsDraftGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsCandidateController extends Controller
{
    public function __construct(
        protected NewsCandidateValidationService $validationService,
        protected NewsDraftGenerationService $draftGenerationService,
    ) {}

    public function index(Request $request): View
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $status = $request->string('status')->toString();
        $region = $request->string('region')->toString();
        $search = trim($request->string('q')->toString());

        $candidates = $this->filteredQuery($request)
            ->orderByDesc('source_published_at')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.news-candidates.index', [
            'candidates' => $candidates,
            'currentStatus' => $status,
            'currentRegion' => $region,
            'searchQuery' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'availableRegions' => config('ai_editorial.regions.priority', []),
        ]);
    }

    public function validateCandidate(NewsCandidate $newsCandidate): RedirectResponse
    {
        $candidate = $this->validationService->validate($newsCandidate);

        if ($candidate->status === 'rejected') {
            return back()->with('status', 'Kandidat ditolak: '.$candidate->rejection_reason);
        }

        return back()->with('status', 'Kandidat AI berhasil divalidasi dan siap ke tahap drafting.');
    }

    public function reject(Request $request, NewsCandidate $newsCandidate): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->validationService->reject($newsCandidate, $validated['reason'] ?? null);

        return back()->with('status', 'Kandidat AI berhasil ditolak.');
    }

    public function reset(NewsCandidate $newsCandidate): RedirectResponse
    {
        $this->validationService->reset($newsCandidate);

        return back()->with('status', 'Kandidat AI dikembalikan ke status pending.');
    }

    public function generateDraft(NewsCandidate $newsCandidate): RedirectResponse
    {
        $article = $this->draftGenerationService->generateDraftForCandidate($newsCandidate);

        return redirect()
            ->route('admin.articles.edit', $article)
            ->with('status', 'Draft artikel AI berhasil dibuat dari kandidat terverifikasi.');
    }

    public function destroy(NewsCandidate $newsCandidate): RedirectResponse
    {
        if ($newsCandidate->article_id !== null) {
            return back()->with('status', 'Kandidat AI yang sudah terhubung ke draft/artikel tidak dapat dihapus.');
        }

        $newsCandidate->delete();

        return back()->with('status', 'Kandidat AI berhasil dihapus.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:validate,reject,reset,delete'],
            'selection_scope' => ['nullable', 'string', 'in:page,filtered,all'],
            'selected_ids' => ['nullable', 'array'],
            'selected_ids.*' => ['integer'],
            'status' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'q' => ['nullable', 'string'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $candidates = $this->resolveBulkSelection($request, $validated['selection_scope'] ?? 'page');

        if ($candidates->isEmpty()) {
            return back()->with('status', 'Tidak ada kandidat AI yang dipilih.');
        }

        $processed = 0;
        $failed = 0;

        foreach ($candidates as $candidate) {
            if ($validated['action'] === 'delete' && $candidate->article_id !== null) {
                $failed++;

                continue;
            }

            match ($validated['action']) {
                'validate' => $this->validationService->validate($candidate),
                'reject' => $this->validationService->reject($candidate, null),
                'reset' => $this->validationService->reset($candidate),
                'delete' => $candidate->delete(),
            };

            $processed++;
        }

        return redirect()
            ->route('admin.news-candidates.index', $this->bulkQueryString($validated))
            ->with('status', $this->buildBulkStatusMessage($processed, $failed));
    }

    protected function baseQuery(): Builder
    {
        return NewsCandidate::query()
            ->with('article:id,title,slug');
    }

    protected function filteredQuery(Request $request): Builder
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);
        $status = $request->string('status')->toString();
        $region = $request->string('region')->toString();
        $search = trim($request->string('q')->toString());

        return $this->baseQuery()
            ->when(in_array($status, ['pending', 'validated', 'rejected', 'drafted'], true), fn (Builder $query) => $query->where('status', $status))
            ->when($region !== '', fn (Builder $query) => $query->where('region', $region))
            ->where(function (Builder $query) use ($dateFrom, $dateTo): void {
                $query->where(function (Builder $datedQuery) use ($dateFrom, $dateTo): void {
                    $datedQuery
                        ->whereNotNull('source_published_at')
                        ->whereDate('source_published_at', '>=', $dateFrom)
                        ->whereDate('source_published_at', '<=', $dateTo);
                })->orWhere(function (Builder $fallbackQuery) use ($dateFrom, $dateTo): void {
                    $fallbackQuery
                        ->whereNull('source_published_at')
                        ->whereDate('created_at', '>=', $dateFrom)
                        ->whereDate('created_at', '<=', $dateTo);
                });
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $like = '%'.$search.'%';

                    $searchQuery
                        ->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('facts_summary', 'like', $like)
                        ->orWhere('source_name', 'like', $like)
                        ->orWhere('source_url', 'like', $like)
                        ->orWhere('region', 'like', $like);
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
            'region' => $validated['region'] ?? null,
            'q' => $validated['q'] ?? null,
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
        ], fn (mixed $value) => $value !== null && $value !== '');
    }

    protected function buildBulkStatusMessage(int $processed, int $failed): string
    {
        if ($processed === 0 && $failed > 0) {
            return 'Bulk action gagal diproses untuk '.$failed.' kandidat AI.';
        }

        if ($failed === 0) {
            return 'Bulk action berhasil diproses untuk '.$processed.' kandidat AI.';
        }

        return 'Bulk action selesai: '.$processed.' kandidat AI berhasil, '.$failed.' gagal.';
    }
}
