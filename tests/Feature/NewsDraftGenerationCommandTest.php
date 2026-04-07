<?php

namespace Tests\Feature;

use App\Models\NewsCandidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsDraftGenerationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_generate_drafts_command_creates_article_from_validated_candidate(): void
    {
        Config::set('ai_editorial.api_key', 'test-key');
        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $candidate = NewsCandidate::factory()->create([
            'status' => 'validated',
            'source_name' => 'ANTARA Kalsel',
            'source_url' => 'https://example.test/berita/ekonomi-kotabaru',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'title' => 'Ekonomi Kotabaru Bergerak, Aktivitas Pelabuhan Ikut Menguat',
                                'excerpt' => 'Pergerakan logistik disebut mulai memberi efek pada ekonomi pesisir Kotabaru.',
                                'content_html' => '<p>Draft artikel hasil AI yang tetap faktual.</p>',
                                'meta_title' => 'Ekonomi Kotabaru Bergerak, Aktivitas Pelabuhan Ikut Menguat',
                                'meta_description' => 'Pergerakan logistik disebut mulai memberi efek pada ekonomi pesisir Kotabaru.',
                                'category_name' => 'Ekonomi',
                                'tags' => ['Kotabaru', 'Pelabuhan', 'Ekonomi'],
                            ], JSON_UNESCAPED_UNICODE),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $this->artisan('news:generate-drafts --limit=1')
            ->assertSuccessful()
            ->expectsOutputToContain('Drafted 1 article(s), failed 0 candidate(s).');

        $this->assertDatabaseHas('articles', [
            'title' => 'Ekonomi Kotabaru Bergerak, Aktivitas Pelabuhan Ikut Menguat',
            'created_by_ai' => 1,
            'source_name' => 'ANTARA Kalsel',
            'source_url' => 'https://example.test/berita/ekonomi-kotabaru',
        ]);

        $this->assertSame('drafted', $candidate->fresh()->status);
        $this->assertNotNull($candidate->fresh()->article_id);
    }
}
