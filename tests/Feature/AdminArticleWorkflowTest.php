<?php

namespace Tests\Feature;

use App\Mail\ArticlePublishedMail;
use App\Mail\ArticleScheduledMail;
use App\Mail\ArticleSubmittedForReviewMail;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wartawan_can_create_and_submit_article_for_review(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'role' => 'wartawan',
        ]);
        $editor = User::factory()->create([
            'role' => 'editor',
            'email' => 'editor@example.com',
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.articles.store'), [
            'title' => 'Judul Berita Penting',
            'slug' => '',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan singkat berita.',
            'tags' => 'Ekonomi, Pelabuhan, Kotabaru',
            'content' => 'Isi artikel lengkap untuk kebutuhan pengujian.',
            'meta_title' => 'Meta Judul',
            'meta_description' => 'Meta description',
            'schema_type' => 'NewsArticle',
            'is_featured' => false,
        ]);

        $article = Article::firstOrFail();

        $response->assertRedirect(route('admin.articles.edit', $article));
        $this->assertSame('judul-berita-penting', $article->slug);
        $this->assertSame('draft', $article->status);
        $this->assertCount(3, $article->tags);

        $this->actingAs($user)
            ->patch(route('admin.articles.submit-review', $article))
            ->assertRedirect(route('admin.articles.edit', $article));

        $this->assertSame('review', $article->refresh()->status);
        Mail::assertQueued(ArticleSubmittedForReviewMail::class, 2);
        Mail::assertQueued(ArticleSubmittedForReviewMail::class, fn (ArticleSubmittedForReviewMail $mail) => $mail->hasTo($editor->email));
        Mail::assertQueued(ArticleSubmittedForReviewMail::class, fn (ArticleSubmittedForReviewMail $mail) => $mail->hasTo($admin->email));
    }

    public function test_editor_can_publish_article(): void
    {
        Mail::fake();

        $author = User::factory()->create([
            'role' => 'wartawan',
            'email' => 'author@example.com',
        ]);
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $article = Article::factory()->create([
            'user_id' => $author->id,
            'status' => 'review',
            'published_at' => null,
        ]);

        $this->actingAs($editor)
            ->patch(route('admin.articles.publish', $article))
            ->assertRedirect(route('admin.articles.edit', $article));

        $article->refresh();

        $this->assertSame('published', $article->status);
        $this->assertNotNull($article->published_at);
        Mail::assertQueued(ArticlePublishedMail::class, fn (ArticlePublishedMail $mail) => $mail->hasTo($author->email));
    }

    public function test_editor_can_schedule_article_publish(): void
    {
        Mail::fake();

        $author = User::factory()->create([
            'role' => 'wartawan',
            'email' => 'author@example.com',
        ]);
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $category = Category::factory()->create();
        $article = Article::factory()->create([
            'status' => 'review',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'published_at' => now()->addHour(),
        ]);

        $this->actingAs($editor)
            ->patch(route('admin.articles.publish', $article))
            ->assertRedirect(route('admin.articles.edit', $article));

        $article->refresh();

        $this->assertSame('review', $article->status);
        $this->assertTrue($article->published_at->isFuture());
        Mail::assertQueued(ArticleScheduledMail::class, fn (ArticleScheduledMail $mail) => $mail->hasTo($author->email));
    }

    public function test_scheduled_publish_command_queues_author_notification_mail(): void
    {
        Mail::fake();

        $author = User::factory()->create([
            'role' => 'wartawan',
            'email' => 'author@example.com',
        ]);
        $article = Article::factory()->create([
            'user_id' => $author->id,
            'status' => 'review',
            'published_at' => now()->subMinute(),
        ]);

        $this->artisan('articles:publish-scheduled')
            ->assertSuccessful();

        $this->assertSame('published', $article->fresh()->status);
        Mail::assertQueued(ArticlePublishedMail::class, fn (ArticlePublishedMail $mail) => $mail->hasTo($author->email));
    }

    public function test_wartawan_cannot_edit_other_users_article(): void
    {
        $user = User::factory()->create([
            'role' => 'wartawan',
        ]);
        $otherUser = User::factory()->create([
            'role' => 'wartawan',
        ]);
        $article = Article::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.articles.edit', $article))
            ->assertForbidden();
    }

    public function test_editor_can_delete_draft_article(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $article = Article::factory()->create([
            'status' => 'draft',
        ]);

        $this->actingAs($editor)
            ->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_wartawan_cannot_delete_article(): void
    {
        $wartawan = User::factory()->create([
            'role' => 'wartawan',
        ]);
        $article = Article::factory()->create([
            'user_id' => $wartawan->id,
            'status' => 'draft',
        ]);

        $this->actingAs($wartawan)
            ->delete(route('admin.articles.destroy', $article))
            ->assertForbidden();

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
        ]);
    }

    public function test_updating_article_resyncs_tags(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $category = Category::factory()->create();
        $article = Article::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->actingAs($editor)
            ->patch(route('admin.articles.update', $article), [
                'title' => $article->title,
                'slug' => $article->slug,
                'category_id' => $category->id,
                'excerpt' => $article->excerpt,
                'tags' => 'Ekonomi, Logistik',
                'content' => $article->content,
                'meta_title' => $article->meta_title,
                'meta_description' => $article->meta_description,
                'schema_type' => $article->schema_type,
                'is_featured' => false,
                'remove_featured_image' => false,
            ])
            ->assertRedirect(route('admin.articles.edit', $article));

        $this->assertSame(
            ['Ekonomi', 'Logistik'],
            $article->fresh()->tags->pluck('name')->sort()->values()->all()
        );
    }

    public function test_admin_can_bulk_delete_selected_articles(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $first = Article::factory()->create([
            'status' => 'draft',
            'updated_at' => now(),
        ]);
        $second = Article::factory()->create([
            'status' => 'review',
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.articles.bulk'), [
                'action' => 'delete',
                'selection_scope' => 'page',
                'selected_ids' => [$first->id, $second->id],
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.articles.index', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]));

        $this->assertDatabaseMissing('articles', ['id' => $first->id]);
        $this->assertDatabaseMissing('articles', ['id' => $second->id]);
    }
}
