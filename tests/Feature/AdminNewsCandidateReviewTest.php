<?php

namespace Tests\Feature;

use App\Models\NewsCandidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNewsCandidateReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_review_news_candidates_index(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $candidate = NewsCandidate::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.news-candidates.index'))
            ->assertOk()
            ->assertSee($candidate->title);
    }

    public function test_editor_can_validate_news_candidate(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $candidate = NewsCandidate::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($editor)
            ->patch(route('admin.news-candidates.validate', $candidate))
            ->assertRedirect();

        $this->assertSame('validated', $candidate->fresh()->status);
    }

    public function test_wartawan_cannot_access_news_candidate_review(): void
    {
        $wartawan = User::factory()->create([
            'role' => 'wartawan',
        ]);

        $this->actingAs($wartawan)
            ->get(route('admin.news-candidates.index'))
            ->assertForbidden();
    }

    public function test_editor_can_delete_news_candidate_without_article(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $candidate = NewsCandidate::factory()->create([
            'article_id' => null,
        ]);

        $this->actingAs($editor)
            ->delete(route('admin.news-candidates.destroy', $candidate))
            ->assertRedirect();

        $this->assertDatabaseMissing('news_candidates', [
            'id' => $candidate->id,
        ]);
    }

    public function test_editor_cannot_delete_news_candidate_that_is_linked_to_article(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $candidate = NewsCandidate::factory()->create([
            'article_id' => \App\Models\Article::factory()->create()->id,
        ]);

        $this->actingAs($editor)
            ->delete(route('admin.news-candidates.destroy', $candidate))
            ->assertRedirect();

        $this->assertDatabaseHas('news_candidates', [
            'id' => $candidate->id,
        ]);
    }

    public function test_editor_can_bulk_delete_unlinked_news_candidates(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $first = NewsCandidate::factory()->create([
            'article_id' => null,
            'created_at' => now(),
        ]);
        $second = NewsCandidate::factory()->create([
            'article_id' => null,
            'created_at' => now(),
        ]);

        $this->actingAs($editor)
            ->post(route('admin.news-candidates.bulk'), [
                'action' => 'delete',
                'selection_scope' => 'page',
                'selected_ids' => [$first->id, $second->id],
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.news-candidates.index', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]));

        $this->assertDatabaseMissing('news_candidates', ['id' => $first->id]);
        $this->assertDatabaseMissing('news_candidates', ['id' => $second->id]);
    }
}
