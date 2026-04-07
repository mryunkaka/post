<?php

namespace Tests\Unit;

use App\Services\NewsSourceFetcherService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsSourceFetcherServiceTest extends TestCase
{
    public function test_it_can_fetch_items_from_rss_feed(): void
    {
        Http::fake([
            'https://example.test/feed.xml' => Http::response(<<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <rss version="2.0">
                    <channel>
                        <item>
                            <title>Pelabuhan Kotabaru Ramai Aktivitas Ekspor</title>
                            <link>https://example.test/berita/pelabuhan-kotabaru</link>
                            <description>Aktivitas ekspor di Pelabuhan Kotabaru meningkat signifikan pada pekan ini.</description>
                            <pubDate>Tue, 07 Apr 2026 08:00:00 +0800</pubDate>
                        </item>
                    </channel>
                </rss>
                XML),
        ]);

        $items = app(NewsSourceFetcherService::class)->fetch((object) [
            'code' => 'test_source',
            'name' => 'Test Source',
            'region' => 'kotabaru',
            'base_url' => 'https://example.test/',
            'feed_url' => 'https://example.test/feed.xml',
        ]);

        $this->assertCount(1, $items);
        $this->assertSame('Pelabuhan Kotabaru Ramai Aktivitas Ekspor', $items->first()['title']);
        $this->assertSame('https://example.test/berita/pelabuhan-kotabaru', $items->first()['source_url']);
    }

    public function test_it_falls_back_to_html_link_extraction_when_feed_is_not_available(): void
    {
        Http::fake([
            'https://example.test/feed.xml' => Http::response('', 404),
            'https://example.test/' => Http::response(<<<'HTML'
                <html><body>
                    <a href="/berita/panen-jagung-tanbu">Panen Jagung Tanbu Cetak Rekor</a>
                    <a href="/category/pemerintahan">Kategori Pemerintahan</a>
                </body></html>
                HTML),
        ]);

        $items = app(NewsSourceFetcherService::class)->fetch((object) [
            'code' => 'test_source',
            'name' => 'Test Source',
            'region' => 'tanah-bumbu',
            'base_url' => 'https://example.test/',
            'feed_url' => 'https://example.test/feed.xml',
        ]);

        $this->assertCount(1, $items);
        $this->assertSame('https://example.test/berita/panen-jagung-tanbu', $items->first()['source_url']);
    }
}
