<?php

namespace App\Services;

use App\Models\Article;
use App\Models\NewsCandidate;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
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
        $candidates = $this->validatedCandidates($limit);

        $summary = [
            'processed' => $candidates->count(),
            'drafted' => 0,
            'failed' => 0,
        ];

        foreach ($candidates as $candidate) {
            try {
                $this->generateDraftForCandidate($candidate, $author);
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

    public function generateDraftForCandidate(NewsCandidate $candidate, ?User $author = null): Article
    {
        $author ??= $this->resolveAuthor();
        $draft = $this->geminiEditorialService->generateDraft($candidate);
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
            'content' => $this->appendSourceAttribution((string) Arr::get($draft, 'content_html', ''), $candidate),
            'tags' => $this->normalizeTags(Arr::get($draft, 'tags', [])),
            'meta_title' => (string) Arr::get($draft, 'meta_title', $candidate->title),
            'meta_description' => (string) Arr::get($draft, 'meta_description', $candidate->excerpt),
            'schema_type' => 'NewsArticle',
            'is_featured' => false,
            'published_at' => null,
            'created_by_ai' => true,
            'review_notes' => $this->reviewNotes($candidate),
            'source_name' => $candidate->source_name,
            'source_url' => $candidate->source_url,
            'source_published_at' => $candidate->source_published_at,
        ]);

        $candidate->forceFill([
            'status' => 'drafted',
            'article_id' => $article->id,
            'rejection_reason' => null,
        ])->save();

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
            ->get();
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

    protected function appendSourceAttribution(string $content, NewsCandidate $candidate): string
    {
        $content = trim($content);
        $attribution = sprintf(
            '<p><strong>Sumber:</strong> <a href="%s" target="_blank" rel="nofollow noopener noreferrer">%s</a>.</p>',
            e($candidate->source_url),
            e($candidate->source_name)
        );

        return $content === '' ? $attribution : $content."\n\n".$attribution;
    }

    protected function reviewNotes(NewsCandidate $candidate): string
    {
        return trim(implode("\n", array_filter([
            'Draft dibuat otomatis oleh AI editorial dan wajib direview editor sebelum publish.',
            'Sumber: '.$candidate->source_name,
            'Link sumber: '.$candidate->source_url,
            $candidate->image_url ? 'Referensi gambar sumber: '.$candidate->image_url : null,
        ])));
    }
}
