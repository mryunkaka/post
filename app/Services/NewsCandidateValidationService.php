<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsCandidate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsCandidateValidationService
{
    public function validate(NewsCandidate $candidate): NewsCandidate
    {
        $reason = $this->firstFailureReason($candidate);

        if ($reason !== null) {
            return $this->reject($candidate, $reason);
        }

        $candidate->forceFill([
            'status' => 'validated',
            'rejection_reason' => null,
        ])->save();

        return $candidate;
    }

    public function reject(NewsCandidate $candidate, ?string $reason = null): NewsCandidate
    {
        $candidate->forceFill([
            'status' => 'rejected',
            'rejection_reason' => $reason ?: 'Kandidat ditolak saat review editorial AI.',
        ])->save();

        return $candidate;
    }

    public function reset(NewsCandidate $candidate): NewsCandidate
    {
        $candidate->forceFill([
            'status' => 'pending',
            'rejection_reason' => null,
        ])->save();

        return $candidate;
    }

    public function firstFailureReason(NewsCandidate $candidate): ?string
    {
        if ($candidate->source_name === '' || $candidate->source_name === null) {
            return 'Nama sumber wajib tersedia.';
        }

        if ($candidate->title === '' || $candidate->title === null) {
            return 'Judul kandidat berita wajib tersedia.';
        }

        if (Str::length(trim($candidate->title)) < config('ai_editorial.validation.min_title_length', 18)) {
            return 'Judul kandidat terlalu pendek untuk standar editorial.';
        }

        if ($candidate->excerpt !== null && Str::length(trim($candidate->excerpt)) < config('ai_editorial.validation.min_excerpt_length', 40)) {
            return 'Ringkasan kandidat terlalu pendek dan belum layak diproses AI.';
        }

        if (! $this->hasValidSourceUrl($candidate->source_url)) {
            return 'Link sumber harus langsung menuju artikel dan memakai URL HTTP/HTTPS yang valid.';
        }

        if ($candidate->source_published_at === null) {
            return 'Tanggal publikasi sumber wajib tersedia untuk verifikasi freshness.';
        }

        if ($candidate->source_published_at->lt(Carbon::now()->subHours(config('ai_editorial.validation.max_source_age_hours', 72)))) {
            return 'Sumber berita terlalu lama untuk batch berita fresh harian.';
        }

        if (! $this->isLocallyRelevant($candidate)) {
            return 'Wilayah kandidat di luar fokus editorial prioritas.';
        }

        if ($this->matchesExistingPublishedArticle($candidate)) {
            return 'Topik kandidat sudah pernah dipublikasikan dan terdeteksi duplikat.';
        }

        if ($this->matchesExistingValidatedCandidate($candidate)) {
            return 'Topik kandidat duplikat dengan kandidat AI lain yang sudah diproses.';
        }

        return null;
    }

    protected function hasValidSourceUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        $validatedUrl = filter_var($url, FILTER_VALIDATE_URL);

        if ($validatedUrl === false) {
            return false;
        }

        $parts = parse_url($url);
        $scheme = Str::lower((string) ($parts['scheme'] ?? ''));
        $path = trim((string) ($parts['path'] ?? ''));

        return in_array($scheme, config('ai_editorial.validation.allowed_schemes', ['http', 'https']), true)
            && $path !== ''
            && $path !== '/';
    }

    protected function hasAllowedRegion(?string $region): bool
    {
        if ($region === null || $region === '') {
            return false;
        }

        return in_array(Str::lower($region), config('ai_editorial.regions.priority', []), true);
    }

    protected function isLocallyRelevant(NewsCandidate $candidate): bool
    {
        if ($this->hasAllowedRegion($candidate->region)) {
            return true;
        }

        $haystack = Str::lower(implode(' ', array_filter([
            $candidate->title,
            $candidate->excerpt,
            $candidate->facts_summary,
            $candidate->source_name,
            $candidate->source_url,
        ])));

        foreach (config('ai_editorial.regions.focus_keywords', []) as $keyword) {
            if (Str::contains($haystack, Str::lower((string) $keyword))) {
                return true;
            }
        }

        return false;
    }

    protected function matchesExistingPublishedArticle(NewsCandidate $candidate): bool
    {
        return Article::query()
            ->where('status', 'published')
            ->whereRaw('LOWER(slug) = ?', [$this->normalizedTitle($candidate->title)])
            ->exists();
    }

    protected function matchesExistingValidatedCandidate(NewsCandidate $candidate): bool
    {
        return NewsCandidate::query()
            ->whereKeyNot($candidate->getKey())
            ->whereIn('status', ['validated', 'drafted'])
            ->whereRaw('LOWER(REPLACE(title, " ", "-")) = ?', [$this->normalizedTitle($candidate->title)])
            ->exists();
    }

    protected function normalizedTitle(string $title): string
    {
        return Str::slug(Str::lower($title));
    }
}
