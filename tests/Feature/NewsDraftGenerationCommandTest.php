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
        Config::set('ai_editorial.generation.min_sources_per_story', 3);
        Config::set('ai_editorial.generation.max_sources_per_story', 4);
        Config::set('ai_editorial.generation.max_images_per_story', 4);
        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $candidate = NewsCandidate::factory()->create([
            'status' => 'validated',
            'source_name' => 'ANTARA Kalsel',
            'source_url' => 'https://example.test/berita/ekonomi-kotabaru',
            'image_url' => 'https://example.test/media/ekonomi-kotabaru.jpg',
            'title' => 'Ekonomi Kotabaru Bergerak Usai Aktivitas Pelabuhan Naik',
            'excerpt' => 'Pergerakan logistik disebut mulai memberi efek pada ekonomi pesisir Kotabaru.',
        ]);
        $relatedCandidate = NewsCandidate::factory()->create([
            'status' => 'validated',
            'source_name' => 'Media Center Kotabaru',
            'source_url' => 'https://example.test/berita/ekonomi-kotabaru-2',
            'image_url' => 'https://example.test/media/ekonomi-kotabaru-2.jpg',
            'title' => 'Aktivitas Pelabuhan Kotabaru Meningkat, Distribusi Barang Ikut Ramai',
            'excerpt' => 'Peningkatan distribusi barang disebut menghidupkan ekonomi pesisir dan pasar lokal.',
        ]);
        $thirdCandidate = NewsCandidate::factory()->create([
            'status' => 'validated',
            'source_name' => 'BPS Kalimantan Selatan',
            'source_url' => 'https://example.test/berita/ekonomi-kotabaru-3',
            'image_url' => 'https://example.test/media/ekonomi-kotabaru-3.jpg',
            'title' => 'Data Distribusi dan Aktivitas Ekonomi Kotabaru Menguat pada Triwulan Awal',
            'excerpt' => 'Data terbaru menunjukkan pergerakan distribusi dan aktivitas ekonomi pesisir Kotabaru ikut meningkat.',
            'facts_summary' => 'Data distribusi, logistik, dan ekonomi Kotabaru pada triwulan awal menunjukkan penguatan aktivitas.',
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
            'featured_image' => 'https://example.test/media/ekonomi-kotabaru.jpg',
        ]);

        $article = \App\Models\Article::query()->where('title', 'Ekonomi Kotabaru Bergerak, Aktivitas Pelabuhan Ikut Menguat')->firstOrFail();

        $this->assertStringContainsString('Media Center Kotabaru - Aktivitas Pelabuhan Kotabaru Meningkat, Distribusi Barang Ikut Ramai', $article->content);
        $this->assertStringContainsString('BPS Kalimantan Selatan - Data Distribusi dan Aktivitas Ekonomi Kotabaru Menguat pada Triwulan Awal', $article->content);
        $this->assertStringContainsString('https://example.test/media/ekonomi-kotabaru-2.jpg', $article->content);
        $this->assertStringContainsString('https://example.test/media/ekonomi-kotabaru-3.jpg', $article->content);
        $this->assertStringContainsString('Jumlah sumber terpakai: 3', $article->review_notes ?? '');

        $this->assertSame('drafted', $candidate->fresh()->status);
        $this->assertSame('drafted', $relatedCandidate->fresh()->status);
        $this->assertSame('drafted', $thirdCandidate->fresh()->status);
        $this->assertNotNull($candidate->fresh()->article_id);
        $this->assertSame($candidate->fresh()->article_id, $relatedCandidate->fresh()->article_id);
        $this->assertSame($candidate->fresh()->article_id, $thirdCandidate->fresh()->article_id);
    }
}
