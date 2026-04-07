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
}
