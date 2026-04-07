<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_bulk_mark_comments_as_spam(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $first = Comment::factory()->create([
            'status' => 'pending',
            'created_at' => now(),
        ]);
        $second = Comment::factory()->create([
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $this->actingAs($editor)
            ->post(route('admin.comments.bulk'), [
                'action' => 'spam',
                'selection_scope' => 'page',
                'selected_ids' => [$first->id, $second->id],
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ])
            ->assertRedirect(route('admin.comments.index', [
                'date_from' => now()->toDateString(),
                'date_to' => now()->toDateString(),
            ]));

        $this->assertDatabaseHas('comments', [
            'id' => $first->id,
            'status' => 'spam',
        ]);
        $this->assertDatabaseHas('comments', [
            'id' => $second->id,
            'status' => 'spam',
        ]);
    }

    public function test_editor_can_delete_single_comment(): void
    {
        $editor = User::factory()->create([
            'role' => 'editor',
        ]);
        $comment = Comment::factory()->create();

        $this->actingAs($editor)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }
}
