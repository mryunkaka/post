<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCommentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_comment_submission_is_stored_as_pending(): void
    {
        Setting::query()->create([
            'group' => 'feature',
            'key' => 'feature_comment_enabled',
            'value' => '1',
            'autoload' => true,
        ]);

        $article = Article::factory()->create([
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->post(route('comments.store', $article->slug), [
            'guest_name' => 'Pembaca Test',
            'guest_email' => 'reader@example.com',
            'content' => 'Komentar uji dari pembaca.',
            'website' => '',
        ])->assertRedirect(route('articles.show', $article->slug));

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'guest_name' => 'Pembaca Test',
            'guest_email' => 'reader@example.com',
            'status' => 'pending',
        ]);
    }

    public function test_honeypot_submission_does_not_store_comment(): void
    {
        Setting::query()->create([
            'group' => 'feature',
            'key' => 'feature_comment_enabled',
            'value' => '1',
            'autoload' => true,
        ]);

        $article = Article::factory()->create([
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $this->post(route('comments.store', $article->slug), [
            'guest_name' => 'Bot',
            'guest_email' => 'bot@example.com',
            'content' => 'Spam',
            'website' => 'https://spam.example.test',
        ])->assertRedirect(route('articles.show', $article->slug));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_reply_to_reply_is_rejected(): void
    {
        Setting::query()->create([
            'group' => 'feature',
            'key' => 'feature_comment_enabled',
            'value' => '1',
            'autoload' => true,
        ]);

        $article = Article::factory()->create([
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $parent = Comment::factory()->create([
            'article_id' => $article->id,
            'status' => 'approved',
        ]);

        $reply = Comment::factory()->create([
            'article_id' => $article->id,
            'parent_id' => $parent->id,
            'status' => 'approved',
        ]);

        $this->from(route('articles.show', $article->slug))
            ->post(route('comments.store', $article->slug), [
                'guest_name' => 'Pembaca Test',
                'guest_email' => 'reader@example.com',
                'content' => 'Reply kedua level.',
                'parent_id' => $reply->id,
                'website' => '',
            ])
            ->assertRedirect(route('articles.show', $article->slug))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_editor_can_approve_comment_from_admin_panel(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);

        $comment = Comment::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($editor)
            ->patch(route('admin.comments.approve', $comment))
            ->assertRedirect();

        $this->assertSame('approved', $comment->fresh()->status);
    }
}
