<?php

namespace App\Services;

use App\Mail\ArticlePublishedMail;
use App\Mail\ArticleScheduledMail;
use App\Mail\ArticleSubmittedForReviewMail;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class EditorialMailService
{
    public function queueArticleSubmittedForReview(Article $article, User $actor): void
    {
        $article->loadMissing(['author', 'category']);

        $this->editorialRecipients()
            ->reject(fn (User $recipient): bool => (int) $recipient->id === (int) $actor->id)
            ->each(function (User $recipient) use ($article, $actor): void {
                Mail::to($recipient->email)->queue(
                    (new ArticleSubmittedForReviewMail($article, $actor))
                        ->onConnection($this->queueConnection())
                        ->onQueue($this->queueName())
                );
            });
    }

    public function queueArticlePublished(Article $article, ?User $actor = null): void
    {
        $article->loadMissing(['author', 'category']);

        $author = $article->author;

        if (! $author instanceof User || ! $author->is_active || blank($author->email)) {
            return;
        }

        Mail::to($author->email)->queue(
            (new ArticlePublishedMail($article, $actor))
                ->onConnection($this->queueConnection())
                ->onQueue($this->queueName())
        );
    }

    public function queueArticleScheduled(Article $article, User $actor): void
    {
        $article->loadMissing(['author', 'category']);

        $author = $article->author;

        if (! $author instanceof User || ! $author->is_active || blank($author->email)) {
            return;
        }

        Mail::to($author->email)->queue(
            (new ArticleScheduledMail($article, $actor))
                ->onConnection($this->queueConnection())
                ->onQueue($this->queueName())
        );
    }

    /**
     * @return Collection<int, User>
     */
    protected function editorialRecipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'editor'])
            ->orderBy('id')
            ->get();
    }

    protected function queueConnection(): ?string
    {
        $connection = config('mail.queue.connection');

        return filled($connection) ? (string) $connection : null;
    }

    protected function queueName(): string
    {
        return (string) config('mail.queue.queue', 'mail');
    }
}
