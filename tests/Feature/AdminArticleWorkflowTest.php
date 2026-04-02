<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wartawan_can_create_and_submit_article_for_review(): void
    {
        $user = User::factory()->create([
            'role' => 'wartawan',
        ]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.articles.store'), [
            'title' => 'Judul Berita Penting',
            'slug' => '',
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan singkat berita.',
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

        $this->actingAs($user)
            ->patch(route('admin.articles.submit-review', $article))
            ->assertRedirect(route('admin.articles.edit', $article));

        $this->assertSame('review', $article->refresh()->status);
    }

    public function test_editor_can_publish_article(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $article = Article::factory()->create([
            'status' => 'review',
            'published_at' => null,
        ]);

        $this->actingAs($editor)
            ->patch(route('admin.articles.publish', $article))
            ->assertRedirect(route('admin.articles.edit', $article));

        $article->refresh();

        $this->assertSame('published', $article->status);
        $this->assertNotNull($article->published_at);
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
}
