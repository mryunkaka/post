<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicArticleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_archived_article_returns_gone_status(): void
    {
        $article = Article::factory()->create([
            'status' => 'published',
            'published_at' => now()->subHour(),
            'archived_at' => now()->subMinute(),
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertStatus(410);
    }
}
