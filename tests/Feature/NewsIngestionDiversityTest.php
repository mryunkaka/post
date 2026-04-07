<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsIngestionDiversityTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_ingest_uses_balanced_per_source_limit(): void
    {
        Config::set('ai_editorial.sources', [
            [
                'code' => 'source_a',
                'name' => 'Source A',
                'base_url' => 'https://a.test/',
                'feed_url' => 'https://a.test/feed.xml',
                'region' => 'kotabaru',
                'type' => 'news_agency',
                'active' => true,
            ],
            [
                'code' => 'source_b',
                'name' => 'Source B',
                'base_url' => 'https://b.test/',
                'feed_url' => 'https://b.test/feed.xml',
                'region' => 'tanah-bumbu',
                'type' => 'news_agency',
                'active' => true,
            ],
        ]);

        Http::fake([
            'https://a.test/feed.xml' => Http::response($this->buildFeed('A', 10)),
            'https://b.test/feed.xml' => Http::response($this->buildFeed('B', 10)),
            'https://a.test/' => Http::response('<html><body></body></html>'),
            'https://b.test/' => Http::response('<html><body></body></html>'),
        ]);

        $this->artisan('news:ingest --limit=6')->assertSuccessful();

        $this->assertDatabaseCount('news_candidates', 6);
        $this->assertSame(3, \App\Models\NewsCandidate::query()->where('source_code', 'source_a')->count());
        $this->assertSame(3, \App\Models\NewsCandidate::query()->where('source_code', 'source_b')->count());
    }

    protected function buildFeed(string $prefix, int $count): string
    {
        $items = '';
        $host = strtolower($prefix).'.test';

        for ($i = 1; $i <= $count; $i++) {
            $items .= <<<XML
<item>
<title>{$prefix} Kotabaru berita {$i} dengan ringkasan editorial daerah</title>
<link>https://{$host}/berita/{$i}</link>
<description>Ringkasan berita {$i} yang cukup panjang untuk lolos validasi editorial daerah dan tetap fokus pada Kotabaru serta Tanah Bumbu.</description>
<pubDate>Tue, 07 Apr 2026 08:00:00 +0800</pubDate>
</item>
XML;
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
{$items}
</channel>
</rss>
XML;
    }
}
