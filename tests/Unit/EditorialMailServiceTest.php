<?php

namespace Tests\Unit;

use App\Mail\ArticlePublishedMail;
use App\Mail\ArticleScheduledMail;
use App\Mail\ArticleSubmittedForReviewMail;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Services\EditorialMailService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EditorialMailServiceTest extends TestCase
{
    public function test_it_queues_review_submission_mail_for_editorial_recipients(): void
    {
        Mail::fake();

        $actor = (new User([
            'id' => 1,
            'name' => 'Wartawan Test',
            'email' => 'wartawan@example.com',
            'role' => 'wartawan',
            'is_active' => true,
        ]))->forceFill(['id' => 1]);

        $editor = (new User([
            'id' => 2,
            'name' => 'Editor Test',
            'email' => 'editor@example.com',
            'role' => 'editor',
            'is_active' => true,
        ]))->forceFill(['id' => 2]);

        $admin = (new User([
            'id' => 3,
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]))->forceFill(['id' => 3]);

        $article = new Article([
            'title' => 'Artikel Uji Review',
            'slug' => 'artikel-uji-review',
            'excerpt' => 'Ringkasan artikel uji.',
        ]);
        $article->setRelation('author', $actor);
        $article->setRelation('category', new Category(['name' => 'Berita']));

        $service = new class(new Collection([$editor, $admin])) extends EditorialMailService
        {
            public function __construct(protected Collection $recipients) {}

            protected function editorialRecipients(): Collection
            {
                return $this->recipients;
            }
        };

        $service->queueArticleSubmittedForReview($article, $actor);

        Mail::assertQueued(ArticleSubmittedForReviewMail::class, 2);
        Mail::assertQueued(ArticleSubmittedForReviewMail::class, fn (ArticleSubmittedForReviewMail $mail) => $mail->hasTo('editor@example.com'));
        Mail::assertQueued(ArticleSubmittedForReviewMail::class, fn (ArticleSubmittedForReviewMail $mail) => $mail->hasTo('admin@example.com'));
    }

    public function test_it_queues_published_mail_for_active_author(): void
    {
        Mail::fake();

        $author = new User([
            'name' => 'Penulis',
            'email' => 'penulis@example.com',
            'role' => 'wartawan',
            'is_active' => true,
        ]);

        $article = new Article([
            'title' => 'Artikel Publish',
            'slug' => 'artikel-publish',
            'published_at' => now(),
        ]);
        $article->setRelation('author', $author);
        $article->setRelation('category', new Category(['name' => 'Berita']));

        app(EditorialMailService::class)->queueArticlePublished($article);

        Mail::assertQueued(ArticlePublishedMail::class, fn (ArticlePublishedMail $mail) => $mail->hasTo('penulis@example.com'));
    }

    public function test_it_queues_scheduled_mail_for_active_author(): void
    {
        Mail::fake();

        $author = new User([
            'name' => 'Penulis',
            'email' => 'penulis@example.com',
            'role' => 'wartawan',
            'is_active' => true,
        ]);

        $editor = new User([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'role' => 'editor',
            'is_active' => true,
        ]);

        $article = new Article([
            'title' => 'Artikel Terjadwal',
            'slug' => 'artikel-terjadwal',
            'published_at' => now()->addHour(),
        ]);
        $article->setRelation('author', $author);
        $article->setRelation('category', new Category(['name' => 'Berita']));

        app(EditorialMailService::class)->queueArticleScheduled($article, $editor);

        Mail::assertQueued(ArticleScheduledMail::class, fn (ArticleScheduledMail $mail) => $mail->hasTo('penulis@example.com'));
    }

    public function test_it_skips_published_mail_when_author_is_inactive(): void
    {
        Mail::fake();

        $author = new User([
            'name' => 'Penulis',
            'email' => 'penulis@example.com',
            'role' => 'wartawan',
            'is_active' => false,
        ]);

        $article = new Article([
            'title' => 'Artikel Publish',
            'slug' => 'artikel-publish',
            'published_at' => now(),
        ]);
        $article->setRelation('author', $author);
        $article->setRelation('category', new Category(['name' => 'Berita']));

        app(EditorialMailService::class)->queueArticlePublished($article);

        Mail::assertNothingQueued();
    }
}
