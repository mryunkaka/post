<?php

namespace Tests\Unit;

use App\Models\NewsCandidate;
use App\Services\NewsCandidateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsCandidateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_store_news_candidate_from_ingestion_payload(): void
    {
        $candidate = app(NewsCandidateService::class)->upsertFromIngestion([
            'source_code' => 'kotabaru_media_center',
            'source_name' => 'Media Center Kotabaru',
            'source_url' => 'https://mediacenter.kotabarukab.go.id/contoh-berita',
            'source_published_at' => '2026-04-08 07:00:00',
            'region' => 'kotabaru',
            'title' => 'Kotabaru fokus dorong pelabuhan rakyat',
            'excerpt' => 'Pemkab Kotabaru menegaskan prioritas pelabuhan rakyat.',
            'image_url' => 'https://mediacenter.kotabarukab.go.id/media/sample.jpg',
            'facts_summary' => 'Fokus pada pelabuhan rakyat dan logistik pesisir.',
            'raw_payload' => ['headline' => 'raw'],
        ]);

        $this->assertSame('pending', $candidate->status);
        $this->assertDatabaseHas('news_candidates', [
            'id' => $candidate->id,
            'source_code' => 'kotabaru_media_center',
            'source_url' => 'https://mediacenter.kotabarukab.go.id/contoh-berita',
        ]);
    }

    public function test_it_updates_existing_candidate_with_same_source_url(): void
    {
        $candidate = NewsCandidate::factory()->create([
            'source_url' => 'https://example.test/berita/a',
            'source_url_hash' => hash('sha256', 'https://example.test/berita/a'),
            'title' => 'Judul Lama',
            'status' => 'pending',
        ]);

        $updatedCandidate = app(NewsCandidateService::class)->upsertFromIngestion([
            'source_code' => 'antara_kalsel',
            'source_name' => 'ANTARA Kalsel',
            'source_url' => 'https://example.test/berita/a',
            'title' => 'Judul Baru',
            'status' => 'validated',
        ]);

        $this->assertTrue($updatedCandidate->is($candidate));
        $this->assertSame('Judul Baru', $updatedCandidate->title);
        $this->assertSame('validated', $updatedCandidate->status);
        $this->assertDatabaseCount('news_candidates', 1);
    }
}
