<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsCandidate;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class NewsDraftGenerationService
{
    public function __construct(
        protected GeminiEditorialService $geminiEditorialService,
        protected CategoryProvisionService $categoryProvisionService,
        protected ArticleService $articleService,
    ) {}

    /**
     * @return array<string, int>
     */
    public function generatePendingDrafts(?int $limit = null): array
    {
        $limit = max(1, $limit ?? (int) config('ai_editorial.generation.default_limit', 10));
        $author = $this->resolveAuthor();
        $poolLimit = max($limit, $limit * max(1, (int) config('ai_editorial.generation.pool_multiplier', 3)));
        $candidatePool = $this->validatedCandidates($poolLimit);
        $candidates = $this->selectPrimaryCandidates($candidatePool, $limit);

        $summary = [
            'processed' => $candidates->count(),
            'drafted' => 0,
            'failed' => 0,
        ];

        foreach ($candidates as $candidate) {
            try {
                $this->generateDraftForCandidate($candidate, $author, $candidatePool);
                $summary['drafted']++;
            } catch (\Throwable $exception) {
                $candidate->forceFill([
                    'status' => 'rejected',
                    'rejection_reason' => 'Generate draft gagal: '.$exception->getMessage(),
                ])->save();
                $summary['failed']++;
            }
        }

        return $summary;
    }

    public function generateDraftForCandidate(NewsCandidate $candidate, ?User $author = null, ?Collection $candidatePool = null): Article
    {
        $author ??= $this->resolveAuthor();
        $candidatePool ??= $this->validatedCandidates((int) config('ai_editorial.generation.pool_multiplier', 3) * 10);
        $sourceBundle = $this->buildSourceBundle($candidate, $candidatePool);
        $draft = $this->geminiEditorialService->generateDraft($candidate, $sourceBundle);
        $category = $this->categoryProvisionService->resolveOrCreate([
            'name' => (string) Arr::get($draft, 'category_name', 'Lokal'),
            'description' => 'Kategori hasil workflow AI editorial.',
            'seo_title' => (string) Arr::get($draft, 'category_name', 'Lokal'),
            'seo_description' => 'Kategori hasil workflow AI editorial.',
            'is_active' => true,
        ]);

        $article = $this->articleService->create($author, [
            'category_id' => $category->id,
            'title' => (string) Arr::get($draft, 'title', $candidate->title),
            'slug' => '',
            'excerpt' => (string) Arr::get($draft, 'excerpt', $candidate->excerpt),
            'content' => $this->appendSourceAttribution((string) Arr::get($draft, 'content_html', ''), $sourceBundle),
            'featured_image' => $this->resolveFeaturedImageUrl($sourceBundle),
            'tags' => $this->normalizeTags(Arr::get($draft, 'tags', [])),
            'meta_title' => (string) Arr::get($draft, 'meta_title', $candidate->title),
            'meta_description' => (string) Arr::get($draft, 'meta_description', $candidate->excerpt),
            'schema_type' => 'NewsArticle',
            'is_featured' => false,
            'published_at' => null,
            'created_by_ai' => true,
            'review_notes' => $this->reviewNotes($sourceBundle),
            'source_name' => $candidate->source_name,
            'source_url' => $candidate->source_url,
            'source_published_at' => $candidate->source_published_at,
        ]);

        foreach ($sourceBundle as $bundleCandidate) {
            $bundleCandidate->forceFill([
                'status' => 'drafted',
                'article_id' => $article->id,
                'rejection_reason' => null,
            ])->save();
        }

        return $article;
    }

    protected function resolveAuthor(): User
    {
        $configuredEmail = trim((string) config('ai_editorial.author_email', ''));

        $query = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'editor']);

        $author = $configuredEmail !== ''
            ? (clone $query)->where('email', $configuredEmail)->first()
            : null;

        $author ??= $query->orderByRaw("FIELD(role, 'admin', 'editor')")->first();

        if (! $author instanceof User) {
            throw new RuntimeException('Tidak ada user admin/editor aktif untuk menjadi author draft AI.');
        }

        return $author;
    }

    /**
     * @return Collection<int, NewsCandidate>
     */
    protected function validatedCandidates(int $limit): Collection
    {
        return NewsCandidate::query()
            ->where('status', 'validated')
            ->whereNull('article_id')
            ->orderByDesc('source_published_at')
            ->take($limit)
            ->get()
            ->values();
    }

    protected function selectPrimaryCandidates(Collection $candidatePool, int $limit): Collection
    {
        $selected = collect();
        $usedSignatures = [];

        foreach ($candidatePool as $candidate) {
            $signature = $this->storySignature($candidate);

            if ($signature === '' || in_array($signature, $usedSignatures, true)) {
                continue;
            }

            $selected->push($candidate);
            $usedSignatures[] = $signature;

            if ($selected->count() >= $limit) {
                break;
            }
        }

        return $selected;
    }

    protected function buildSourceBundle(NewsCandidate $candidate, Collection $candidatePool): Collection
    {
        $primaryKeywords = $this->storyKeywords($candidate);
        $primarySignature = $this->storySignature($candidate);
        $minSources = max(1, (int) config('ai_editorial.generation.min_sources_per_story', 3));
        $maxSources = max(1, (int) config('ai_editorial.generation.max_sources_per_story', 4));
        $ranked = $candidatePool
            ->filter(function (NewsCandidate $item) use ($candidate): bool {
                return $item->getKey() !== $candidate->getKey()
                    && $item->article_id === null
                    && $item->status === 'validated'
                    && $item->source_url !== $candidate->source_url;
            })
            ->map(function (NewsCandidate $item) use ($candidate, $primaryKeywords, $primarySignature): array {
                return [
                    'candidate' => $item,
                    'score' => $this->relatednessScore($candidate, $item, $primaryKeywords, $primarySignature),
                ];
            })
            ->filter(fn (array $entry): bool => $entry['score'] > 0)
            ->sortByDesc('score')
            ->values();

        $bundle = collect([$candidate]);
        $usedSources = [$candidate->source_name];

        foreach ($ranked as $entry) {
            /** @var NewsCandidate $relatedCandidate */
            $relatedCandidate = $entry['candidate'];

            if ($bundle->count() >= $maxSources) {
                break;
            }

            if ($bundle->count() < $minSources && ! in_array($relatedCandidate->source_name, $usedSources, true)) {
                $bundle->push($relatedCandidate);
                $usedSources[] = $relatedCandidate->source_name;

                continue;
            }

            if (! $bundle->contains(fn (NewsCandidate $item) => $item->source_url === $relatedCandidate->source_url)) {
                $bundle->push($relatedCandidate);
            }
        }

        if ($bundle->count() < $minSources) {
            foreach ($ranked as $entry) {
                /** @var NewsCandidate $relatedCandidate */
                $relatedCandidate = $entry['candidate'];

                if ($bundle->count() >= $minSources || $bundle->count() >= $maxSources) {
                    break;
                }

                if (! $bundle->contains(fn (NewsCandidate $item) => $item->source_url === $relatedCandidate->source_url)) {
                    $bundle->push($relatedCandidate);
                }
            }
        }

        return $bundle
            ->unique('source_url')
            ->values();
    }

    /**
     * @param  mixed  $tags
     */
    protected function normalizeTags(mixed $tags): string
    {
        if (is_string($tags)) {
            return $tags;
        }

        if (is_array($tags)) {
            return collect($tags)
                ->map(fn ($tag) => trim((string) $tag))
                ->filter()
                ->take(8)
                ->implode(', ');
        }

        return '';
    }

    protected function appendSourceAttribution(string $content, Collection $sources): string
    {
        $content = trim($content);
        $gallery = $this->appendImageGallery($sources);
        $items = $sources
            ->unique('source_url')
            ->map(fn (NewsCandidate $source): string => sprintf(
                '<li><a href="%s" target="_blank" rel="nofollow noopener noreferrer">%s</a></li>',
                e($source->source_url),
                e($source->source_name.' - '.$source->title)
            ))
            ->implode('');
        $attribution = '<div><p><strong>Sumber rujukan:</strong></p><ul>'.$items.'</ul></div>';
        $parts = array_filter([$content, $gallery, $attribution]);

        return implode("\n\n", $parts);
    }

    protected function reviewNotes(Collection $sources): string
    {
        $lines = [
            'Draft dibuat otomatis oleh AI editorial dan wajib direview editor sebelum publish.',
            'Jumlah sumber terpakai: '.$sources->unique('source_url')->count(),
        ];

        foreach ($sources->unique('source_url') as $source) {
            $lines[] = 'Sumber: '.$source->source_name;
            $lines[] = 'Link sumber: '.$source->source_url;

            if ($source->image_url) {
                $lines[] = 'Referensi gambar sumber: '.$source->image_url;
            }
        }

        return trim(implode("\n", $lines));
    }

    protected function appendImageGallery(Collection $sources): ?string
    {
        $maxImages = max(1, (int) config('ai_editorial.generation.max_images_per_story', 4));
        $images = $sources
            ->map(function (NewsCandidate $source): ?array {
                $url = trim((string) ($source->image_url ?? ''));

                if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
                    return null;
                }

                return [
                    'url' => $url,
                    'caption' => $source->source_name.' - '.$source->title,
                ];
            })
            ->filter()
            ->unique('url')
            ->take($maxImages)
            ->values();

        if ($images->isEmpty()) {
            return null;
        }

        $figures = $images->map(fn (array $image): string => sprintf(
            '<figure><img src="%s" alt="%s" loading="lazy" referrerpolicy="no-referrer" /><figcaption>%s</figcaption></figure>',
            e($image['url']),
            e($image['caption']),
            e($image['caption'])
        ))->implode('');

        return '<div class="ai-source-gallery">'.$figures.'</div>';
    }

    protected function resolveFeaturedImageUrl(Collection $sources): ?string
    {
        $imageUrl = $sources
            ->map(fn (NewsCandidate $source) => trim((string) ($source->image_url ?? '')))
            ->first(fn (string $url) => $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false);

        return $imageUrl ?: null;
    }

    /**
     * @return list<string>
     */
    protected function storyKeywords(NewsCandidate $candidate): array
    {
        $text = Str::lower(implode(' ', array_filter([
            $candidate->title,
            $candidate->excerpt,
            $candidate->facts_summary,
            $candidate->region,
        ])));

        $text = preg_replace('/[^a-z0-9\s-]/', ' ', $text) ?? $text;
        $tokens = collect(preg_split('/\s+/', $text) ?: [])
            ->map(fn (string $token) => trim($token))
            ->filter(fn (string $token) => $token !== '' && strlen($token) >= 4)
            ->reject(fn (string $token) => in_array($token, $this->stopwords(), true))
            ->unique()
            ->values()
            ->all();

        return array_slice($tokens, 0, 8);
    }

    protected function storySignature(NewsCandidate $candidate): string
    {
        $keywords = $this->storyKeywords($candidate);

        return implode('-', array_slice($keywords, 0, 3));
    }

    /**
     * @param  list<string>  $left
     * @param  list<string>  $right
     */
    protected function keywordOverlap(array $left, array $right): int
    {
        return count(array_intersect($left, $right));
    }

    /**
     * @param  list<string>  $primaryKeywords
     */
    protected function relatednessScore(NewsCandidate $primary, NewsCandidate $candidate, array $primaryKeywords, string $primarySignature): int
    {
        $score = 0;

        if ($this->storySignature($candidate) === $primarySignature && $primarySignature !== '') {
            $score += 8;
        }

        $score += $this->keywordOverlap($primaryKeywords, $this->storyKeywords($candidate)) * 3;

        if ($candidate->region !== null && $candidate->region === $primary->region) {
            $score += 3;
        }

        if ($candidate->source_name !== $primary->source_name) {
            $score += 2;
        }

        $sourceName = Str::lower($candidate->source_name ?? '');

        if (Str::contains($sourceName, ['antara', 'bmkg', 'bps', 'kementerian', 'pemprov', 'pemkab', 'media center'])) {
            $score += 1;
        }

        return $score;
    }

    /**
     * @return list<string>
     */
    protected function stopwords(): array
    {
        return [
            'yang', 'dengan', 'untuk', 'pada', 'dari', 'dalam', 'akan', 'telah', 'para', 'warga',
            'kabar', 'tahun', 'resmi', 'tingkat', 'hingga', 'setelah', 'karena', 'lebih', 'sebagai',
            'berita', 'daerah', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'media', 'center',
            'pemkab', 'pemprov', 'antara', 'nasional', 'selatan', 'utara', 'timur', 'barat',
        ];
    }
}
