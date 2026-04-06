<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CommentService
{
    public function __construct(
        protected SettingService $settingService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createGuestComment(Article $article, array $data, Request $request): ?Comment
    {
        $this->assertCommentingEnabled();
        $this->assertArticleCanReceiveComments($article);

        if ($this->isHoneypotTriggered($data)) {
            return null;
        }

        $parent = $this->resolveParentComment($article, $data['parent_id'] ?? null);
        $content = $this->sanitizeContent((string) $data['content']);

        if ($content === '') {
            throw ValidationException::withMessages([
                'content' => 'Komentar tidak boleh kosong.',
            ]);
        }

        return Comment::query()->create([
            'article_id' => $article->id,
            'user_id' => $request->user()?->id,
            'parent_id' => $parent?->id,
            'guest_name' => $request->user()?->name ?? trim((string) $data['guest_name']),
            'guest_email' => $request->user()?->email ?? trim((string) $data['guest_email']),
            'content' => $content,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function approve(Comment $comment): Comment
    {
        return $this->setStatus($comment, 'approved');
    }

    public function reject(Comment $comment): Comment
    {
        return $this->setStatus($comment, 'rejected');
    }

    public function markAsSpam(Comment $comment): Comment
    {
        return $this->setStatus($comment, 'spam');
    }

    public function commentsEnabled(): bool
    {
        return filter_var($this->settingService->get('feature_comment_enabled', false), FILTER_VALIDATE_BOOL);
    }

    protected function assertCommentingEnabled(): void
    {
        if (! $this->commentsEnabled()) {
            throw new AuthorizationException('Fitur komentar sedang nonaktif.');
        }
    }

    protected function assertArticleCanReceiveComments(Article $article): void
    {
        if (
            $article->archived_at !== null
            || $article->status !== 'published'
            || $article->published_at === null
            || $article->published_at->isFuture()
        ) {
            throw new AuthorizationException('Artikel ini tidak menerima komentar publik.');
        }
    }

    protected function isHoneypotTriggered(array $data): bool
    {
        return trim((string) ($data['website'] ?? '')) !== '';
    }

    protected function resolveParentComment(Article $article, mixed $parentId): ?Comment
    {
        if ($parentId === null || $parentId === '') {
            return null;
        }

        $parent = Comment::query()
            ->where('article_id', $article->id)
            ->whereKey((int) $parentId)
            ->approved()
            ->first();

        if (! $parent instanceof Comment) {
            throw ValidationException::withMessages([
                'parent_id' => 'Komentar induk tidak valid.',
            ]);
        }

        if ($parent->parent_id !== null) {
            throw ValidationException::withMessages([
                'parent_id' => 'Balasan hanya diizinkan maksimal satu level.',
            ]);
        }

        return $parent;
    }

    protected function sanitizeContent(string $content): string
    {
        $content = trim(strip_tags($content));
        $content = preg_replace("/\r\n|\r/", "\n", $content) ?? $content;
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return $content;
    }

    protected function setStatus(Comment $comment, string $status): Comment
    {
        $comment->forceFill([
            'status' => $status,
        ])->save();

        return $comment->refresh();
    }
}
