<?php

namespace App\Services;

class NewsIngestionService
{
    public function __construct(
        protected SourceRegistryService $sourceRegistry,
        protected NewsSourceFetcherService $fetcher,
        protected NewsCandidateService $candidateService,
        protected NewsCandidateValidationService $validationService,
    ) {}

    /**
     * @return array<string, int>
     */
    public function ingest(?string $sourceCode = null, ?int $limit = null): array
    {
        $remaining = max(1, $limit ?? (int) config('ai_editorial.ingest.default_limit', 10));
        $sources = $sourceCode === null
            ? $this->sourceRegistry->active()
            : $this->sourceRegistry->active()->where('code', $sourceCode)->values();

        $summary = [
            'sources' => $sources->count(),
            'fetched' => 0,
            'stored' => 0,
            'validated' => 0,
            'rejected' => 0,
        ];

        foreach ($sources as $source) {
            if ($remaining <= 0) {
                break;
            }

            $items = $this->fetcher->fetch($source, $remaining);
            $summary['fetched'] += $items->count();

            foreach ($items as $item) {
                if ($remaining <= 0) {
                    break;
                }

                $candidate = $this->candidateService->upsertFromIngestion($item);
                $candidate = $this->validationService->validate($candidate);

                $summary['stored']++;

                if ($candidate->status === 'validated') {
                    $summary['validated']++;
                }

                if ($candidate->status === 'rejected') {
                    $summary['rejected']++;
                }

                $remaining--;
            }
        }

        return $summary;
    }
}
