<?php

namespace Tests\Unit;

use App\Models\NewsCandidate;
use App\Services\GeminiEditorialService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiEditorialServiceTest extends TestCase
{
    public function test_it_generates_structured_draft_payload_from_gemini_response(): void
    {
        Config::set('ai_editorial.api_key', 'test-key');
        Config::set('ai_editorial.model', 'gemini-2.5-flash-lite');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'title' => 'Pelabuhan Kotabaru Naikkan Harapan Ekonomi Pesisir',
                                'excerpt' => 'Aktivitas logistik di Kotabaru disebut ikut menggerakkan ekonomi pesisir.',
                                'content_html' => '<p>Isi berita ringkas dan rapi.</p>',
                                'meta_title' => 'Pelabuhan Kotabaru Naikkan Harapan Ekonomi Pesisir',
                                'meta_description' => 'Aktivitas logistik di Kotabaru disebut ikut menggerakkan ekonomi pesisir.',
                                'category_name' => 'Ekonomi',
                                'tags' => ['Kotabaru', 'Pelabuhan', 'Logistik'],
                            ], JSON_UNESCAPED_UNICODE),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $payload = app(GeminiEditorialService::class)->generateDraft(NewsCandidate::factory()->make());

        $this->assertSame('Ekonomi', $payload['category_name']);
        $this->assertSame('Pelabuhan Kotabaru Naikkan Harapan Ekonomi Pesisir', $payload['title']);
    }
}
