<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;

class ArticleService
{
    public function __construct(
        protected SlugService $slugService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $actor, array $data): Article
    {
        $article = new Article;

        $article->fill($this->preparePayload($data, $article));
        $article->author()->associate($actor);
        $article->status = 'draft';
        $article->published_at = null;
        $article->save();

        return $article->load(['author', 'category']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $actor, Article $article, array $data): Article
    {
        $this->assertCanEdit($actor, $article);

        $article->fill($this->preparePayload($data, $article));
        $article->save();

        return $article->load(['author', 'category']);
    }

    public function submitForReview(User $actor, Article $article): Article
    {
        $this->assertCanEdit($actor, $article);

        if ($article->status === 'published') {
            throw new AuthorizationException('Artikel yang sudah dipublish tidak dapat diajukan ulang ke review.');
        }

        $article->forceFill([
            'status' => 'review',
            'review_notes' => null,
            'published_at' => null,
        ])->save();

        return $article->refresh();
    }

    public function publish(User $actor, Article $article): Article
    {
        if (! in_array($actor->role, ['admin', 'editor'], true)) {
            throw new AuthorizationException('Hanya admin dan editor yang dapat mempublish artikel.');
        }

        if ($article->archived_at !== null) {
            throw new AuthorizationException('Artikel arsip tidak dapat dipublish.');
        }

        $article->forceFill([
            'status' => 'published',
            'published_at' => now(),
        ])->save();

        return $article->refresh();
    }

    public function delete(User $actor, Article $article): void
    {
        if ($actor->role === 'admin') {
            $article->delete();

            return;
        }

        if ($actor->role === 'editor' && in_array($article->status, ['draft', 'review'], true)) {
            $article->delete();

            return;
        }

        throw new AuthorizationException('Anda tidak memiliki izin menghapus artikel ini.');
    }

    public function assertCanView(User $actor, Article $article): void
    {
        if (in_array($actor->role, ['admin', 'editor'], true)) {
            return;
        }

        if (! $this->ownsArticle($actor, $article)) {
            throw new AuthorizationException('Anda tidak memiliki akses ke artikel ini.');
        }
    }

    public function assertCanEdit(User $actor, Article $article): void
    {
        if (in_array($actor->role, ['admin', 'editor'], true)) {
            return;
        }

        if (! $this->ownsArticle($actor, $article)) {
            throw new AuthorizationException('Anda hanya dapat mengubah artikel milik sendiri.');
        }

        if ($article->status === 'published') {
            throw new AuthorizationException('Artikel yang sudah dipublish tidak dapat diubah oleh wartawan.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function preparePayload(array $data, Article $article): array
    {
        $title = trim((string) $data['title']);
        $slugSource = trim((string) ($data['slug'] ?: $title));

        return [
            'category_id' => $data['category_id'],
            'title' => $title,
            'slug' => $this->slugService->generateUniqueSlug($slugSource, Article::class, 'slug', $article),
            'excerpt' => $this->nullableString($data, 'excerpt'),
            'content' => trim((string) $data['content']),
            'meta_title' => $this->nullableString($data, 'meta_title'),
            'meta_description' => $this->nullableString($data, 'meta_description'),
            'schema_type' => Arr::get($data, 'schema_type', 'NewsArticle') ?: 'NewsArticle',
            'is_featured' => (bool) Arr::get($data, 'is_featured', false),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function nullableString(array $data, string $key): ?string
    {
        $value = Arr::get($data, $key);

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function ownsArticle(User $actor, Article $article): bool
    {
        return (int) $article->user_id === (int) $actor->id;
    }
}
