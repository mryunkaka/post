<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsIngestionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_ingest_command_fetches_and_stores_candidates(): void
    {
        Config::set('ai_editorial.sources', [[
            'code' => 'test_source',
            'name' => 'Test Source',
            'base_url' => 'https://example.test/',
            'feed_url' => 'https://example.test/feed.xml',
            'region' => 'kotabaru',
            'type' => 'news_agency',
            'active' => true,
        ]]);

        Http::fake([
            'https://example.test/feed.xml' => Http::response(<<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <rss version="2.0">
                    <channel>
                        <item>
                            <title>Pelabuhan Kotabaru Dorong Ekonomi Pesisir Pekan Ini</title>
                            <link>https://example.test/berita/pelabuhan-kotabaru-ekonomi</link>
                            <description>Peningkatan aktivitas pelabuhan mendorong distribusi logistik di wilayah pesisir Kotabaru.</description>
                            <pubDate>Tue, 07 Apr 2026 08:00:00 +0800</pubDate>
                        </item>
                    </channel>
                </rss>
                XML),
        ]);

        $this->artisan('news:ingest --limit=5')
            ->assertSuccessful()
            ->expectsOutputToContain('Processed 1 source(s).');

        $this->assertDatabaseHas('news_candidates', [
            'source_code' => 'test_source',
            'status' => 'validated',
            'title' => 'Pelabuhan Kotabaru Dorong Ekonomi Pesisir Pekan Ini',
        ]);
    }
}
