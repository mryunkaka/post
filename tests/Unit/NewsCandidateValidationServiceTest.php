<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\NewsCandidate;
use App\Services\NewsCandidateValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsCandidateValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_candidate_that_meets_editorial_rules(): void
    {
        $candidate = NewsCandidate::factory()->create([
            'title' => 'Aktivitas Pelabuhan Kotabaru Meningkat Signifikan Awal Pekan Ini',
        ]);

        $service = app(NewsCandidateValidationService::class);

        $service->validate($candidate);

        $this->assertSame('validated', $candidate->fresh()->status);
        $this->assertNull($candidate->fresh()->rejection_reason);
    }

    public function test_it_rejects_candidate_when_source_is_too_old(): void
    {
        $candidate = NewsCandidate::factory()->create([
            'source_published_at' => now()->subDays(7),
        ]);

        $service = app(NewsCandidateValidationService::class);

        $service->validate($candidate);

        $this->assertSame('rejected', $candidate->fresh()->status);
        $this->assertSame('Sumber berita terlalu lama untuk batch berita fresh harian.', $candidate->fresh()->rejection_reason);
    }

    public function test_it_rejects_candidate_that_duplicates_existing_published_article(): void
    {
        Article::factory()->create([
            'title' => 'Aktivitas Pelabuhan Kotabaru Meningkat Signifikan Awal Pekan Ini',
            'slug' => 'aktivitas-pelabuhan-kotabaru-meningkat-signifikan-awal-pekan-ini',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        $candidate = NewsCandidate::factory()->create([
            'title' => 'Aktivitas Pelabuhan Kotabaru Meningkat Signifikan Awal Pekan Ini',
        ]);

        $service = app(NewsCandidateValidationService::class);

        $service->validate($candidate);

        $this->assertSame('rejected', $candidate->fresh()->status);
        $this->assertSame('Topik kandidat sudah pernah dipublikasikan dan terdeteksi duplikat.', $candidate->fresh()->rejection_reason);
    }
}
