<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsCandidate;
use App\Services\NewsDraftGenerationService;
use App\Services\NewsCandidateValidationService;
use Illuminate\Contracts\View\View;
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
        $status = $request->string('status')->toString();
        $region = $request->string('region')->toString();

        $candidates = NewsCandidate::query()
            ->with('article:id,title,slug')
            ->when(in_array($status, ['pending', 'validated', 'rejected', 'drafted'], true), fn ($query) => $query->where('status', $status))
            ->when($region !== '', fn ($query) => $query->where('region', $region))
            ->orderByDesc('source_published_at')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.news-candidates.index', [
            'candidates' => $candidates,
            'currentStatus' => $status,
            'currentRegion' => $region,
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
}
