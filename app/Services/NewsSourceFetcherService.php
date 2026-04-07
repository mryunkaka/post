<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NewsSourceFetcherService
{
    public function __construct(
        protected HttpFactory $http,
    ) {}

    public function fetch(object $source, int $limit = 10): Collection
    {
        $limit = max(1, $limit);

        $items = $this->fetchFromFeed($source, $limit);

        if ($items->isNotEmpty()) {
            return $items;
        }

        return $this->fetchFromHtml($source, $limit);
    }

    protected function fetchFromFeed(object $source, int $limit): Collection
    {
        $feedUrls = collect($source->feed_urls ?? [])
            ->prepend((string) ($source->feed_url ?? ''))
            ->map(fn (mixed $url) => trim((string) $url))
            ->filter()
            ->unique()
            ->values();

        if ($feedUrls->isEmpty()) {
            return collect();
        }

        $items = collect();

        foreach ($feedUrls as $feedUrl) {
            $response = $this->http
                ->timeout((int) config('ai_editorial.ingest.timeout_seconds', 20))
                ->get($feedUrl);

            if (! $response->successful()) {
                continue;
            }

            $items = $items->concat($this->parseXmlFeed((string) $response->body(), $source, $limit));

            if ($items->count() >= $limit) {
                break;
            }
        }

        return $items
            ->filter()
            ->unique('source_url')
            ->take($limit)
            ->values();
    }

    protected function parseXmlFeed(string $xml, object $source, int $limit): Collection
    {
        if (trim($xml) === '') {
            return collect();
        }

        libxml_use_internal_errors(true);
        $feed = simplexml_load_string($xml, \SimpleXMLElement::class, LIBXML_NOCDATA);
        libxml_clear_errors();

        if (! $feed instanceof \SimpleXMLElement) {
            return collect();
        }

        $items = collect();

        if (isset($feed->channel->item)) {
            foreach ($feed->channel->item as $item) {
                $items->push($this->mapRssItem($item, $source));
            }
        } elseif (isset($feed->entry)) {
            foreach ($feed->entry as $entry) {
                $items->push($this->mapAtomItem($entry, $source));
            }
        }

        return $items
            ->filter()
            ->unique('source_url')
            ->take($limit)
            ->values();
    }

    protected function mapRssItem(\SimpleXMLElement $item, object $source): ?array
    {
        $namespaces = $item->getNamespaces(true);
        $media = isset($namespaces['media']) ? $item->children($namespaces['media']) : null;
        $encoded = isset($namespaces['content']) ? $item->children($namespaces['content']) : null;

        $sourceUrl = trim((string) $item->link);

        if ($sourceUrl === '') {
            return null;
        }

        $description = trim(strip_tags((string) $item->description));
        $contentEncoded = $encoded ? trim(strip_tags((string) $encoded->encoded)) : null;
        $excerpt = $description !== '' ? $description : $contentEncoded;

        $imageUrl = null;

        if ($media && isset($media->content)) {
            $attributes = $media->content->attributes();
            $imageUrl = trim((string) ($attributes['url'] ?? ''));
        }

        if ($imageUrl === null || $imageUrl === '') {
            $enclosure = $item->enclosure;
            if ($enclosure) {
                $attributes = $enclosure->attributes();
                $imageUrl = trim((string) ($attributes['url'] ?? ''));
            }
        }

        return $this->buildPayload(
            $source,
            $sourceUrl,
            trim((string) $item->title),
            $excerpt,
            $imageUrl,
            $this->parseDate((string) $item->pubDate),
            [
                'channel_item' => json_decode(json_encode($item), true),
            ],
        );
    }

    protected function mapAtomItem(\SimpleXMLElement $entry, object $source): ?array
    {
        $sourceUrl = trim((string) ($entry->id ?? ''));

        if (isset($entry->link)) {
            foreach ($entry->link as $link) {
                $attributes = $link->attributes();
                $href = trim((string) ($attributes['href'] ?? ''));

                if ($href !== '') {
                    $sourceUrl = $href;
                    break;
                }
            }
        }

        if ($sourceUrl === '') {
            return null;
        }

        $excerpt = trim(strip_tags((string) ($entry->summary ?? $entry->content ?? '')));

        return $this->buildPayload(
            $source,
            $sourceUrl,
            trim((string) $entry->title),
            $excerpt,
            null,
            $this->parseDate((string) ($entry->updated ?? $entry->published ?? '')),
            [
                'entry' => json_decode(json_encode($entry), true),
            ],
        );
    }

    protected function fetchFromHtml(object $source, int $limit): Collection
    {
        $baseUrl = (string) ($source->base_url ?? '');

        if ($baseUrl === '') {
            return collect();
        }

        $response = $this->http
            ->timeout((int) config('ai_editorial.ingest.timeout_seconds', 20))
            ->get($baseUrl);

        if (! $response->successful()) {
            return collect();
        }

        $html = (string) $response->body();

        if (trim($html) === '') {
            return collect();
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        libxml_clear_errors();

        $links = collect();

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            $href = trim((string) $anchor->getAttribute('href'));
            $title = trim((string) $anchor->textContent);
            $resolvedUrl = $this->resolveUrl($baseUrl, $href);

            if (! $this->isCandidateArticleUrl($baseUrl, $resolvedUrl) || $title === '') {
                continue;
            }

            $links->push($this->buildPayload(
                $source,
                $resolvedUrl,
                $title,
                null,
                null,
                now(),
                [
                    'href' => $href,
                    'source' => 'html_fallback',
                ],
            ));
        }

        return $links
            ->unique('source_url')
            ->take($limit)
            ->values();
    }

    protected function buildPayload(
        object $source,
        string $sourceUrl,
        string $title,
        ?string $excerpt,
        ?string $imageUrl,
        ?string $publishedAt,
        array $rawPayload,
    ): ?array {
        $title = trim($title);
        $sourceUrl = trim($sourceUrl);

        if ($title === '' || $sourceUrl === '') {
            return null;
        }

        return [
            'source_code' => (string) $source->code,
            'source_name' => (string) $source->name,
            'source_url' => $sourceUrl,
            'source_published_at' => $publishedAt,
            'region' => (string) $source->region,
            'title' => $title,
            'excerpt' => $excerpt ? Str::squish($excerpt) : null,
            'image_url' => $imageUrl ?: null,
            'facts_summary' => $excerpt ? Str::limit(Str::squish($excerpt), 240) : null,
            'raw_payload' => $rawPayload,
        ];
    }

    protected function parseDate(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            return now()->parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function resolveUrl(string $baseUrl, string $href): string
    {
        if ($href === '') {
            return '';
        }

        if (Str::startsWith($href, ['http://', 'https://'])) {
            return $href;
        }

        if (Str::startsWith($href, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https';

            return $scheme.':'.$href;
        }

        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';

        if ($host === '') {
            return '';
        }

        if (Str::startsWith($href, '/')) {
            return $scheme.'://'.$host.$href;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($href, '/');
    }

    protected function isCandidateArticleUrl(string $baseUrl, string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $baseHost = parse_url($baseUrl, PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);
        $path = trim((string) parse_url($url, PHP_URL_PATH));

        if ($baseHost === null || $host === null || ! Str::contains($host, $baseHost)) {
            return false;
        }

        if ($path === '' || $path === '/' || Str::contains($path, ['/category/', '/tag/', '/author/', '/page/'])) {
            return false;
        }

        return ! Str::endsWith($path, ['/feed', '/feed/']);
    }
}
