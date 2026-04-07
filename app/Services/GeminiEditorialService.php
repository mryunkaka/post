<?php

namespace App\Services;

use App\Models\NewsCandidate;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class GeminiEditorialService
{
    public function __construct(
        protected HttpFactory $http,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function generateDraft(NewsCandidate $candidate, ?Collection $sources = null): array
    {
        $apiKey = (string) config('ai_editorial.api_key', '');
        $model = (string) config('ai_editorial.model', 'gemini-2.5-flash-lite');
        $endpoint = rtrim((string) config('ai_editorial.generation.endpoint', ''), '/');
        $sources ??= collect([$candidate]);

        if ($apiKey === '') {
            throw new RuntimeException('AI editorial API key belum dikonfigurasi.');
        }

        $response = $this->http
            ->timeout((int) config('ai_editorial.generation.timeout_seconds', 40))
            ->withQueryParameters(['key' => $apiKey])
            ->post($endpoint.'/'.$model.':generateContent', [
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ],
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => $this->buildPrompt($candidate, $sources),
                    ]],
                ]],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini editorial request gagal: '.$response->status());
        }

        $text = Arr::get($response->json(), 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Gemini editorial tidak mengembalikan payload draft yang bisa dipakai.');
        }

        $payload = json_decode($this->normalizeJson((string) $text), true);

        if (! is_array($payload)) {
            throw new RuntimeException('Payload Gemini editorial bukan JSON yang valid.');
        }

        return $payload;
    }

    protected function buildPrompt(NewsCandidate $candidate, Collection $sources): string
    {
        $sourceBrief = $sources
            ->values()
            ->map(function (NewsCandidate $item, int $index): string {
                $number = $index + 1;

                return <<<TEXT
Sumber {$number}:
- Media: {$item->source_name}
- URL: {$item->source_url}
- Tanggal: {$item->source_published_at?->toDateTimeString()}
- Wilayah: {$item->region}
- Judul: {$item->title}
- Ringkasan: {$item->excerpt}
- Fakta ringkas: {$item->facts_summary}
TEXT;
            })
            ->implode("\n\n");
        $wordTarget = (int) config('ai_editorial.generation.min_word_target', 550);

        return <<<PROMPT
Anda adalah wartawan profesional untuk portal berita daerah Indonesia. Tugas Anda menulis draft berita yang faktual, enak dibaca, tidak halu, dan tetap mengakui sumber asli.

Aturan keras:
- Jangan menambah fakta yang tidak ada di data sumber.
- Jangan mengaku melihat langsung di lapangan.
- Judul harus menarik, profesional, masuk akal, dan bukan clickbait kosong.
- Narasi harus rapi, nyambung, mudah dipahami, dan tidak membosankan.
- Gunakan bahasa Indonesia yang natural untuk portal berita.
- Buat kategori paling cocok. Jika kategori belum ada, beri nama kategori baru yang ringkas.
- Tampilkan tag yang relevan.
- Jangan menyalin mentah isi sumber.
- Jika ada beberapa sumber terkait, gabungkan fakta yang saling menguatkan menjadi satu narasi yang utuh.
- Jika sumber membahas topik yang sama dengan sudut berbeda, rangkum menjadi satu artikel komprehensif.
- Panjang artikel minimal sekitar {$wordTarget} kata, ideal 7 sampai 10 paragraf isi utama.
- Artikel harus memiliki pembuka kuat, konteks, rincian fakta, dampak/arti berita, dan penutup yang jelas.
- Jangan membuat artikel pendek dangkal.

Keluarkan JSON object saja tanpa markdown dengan field:
title, excerpt, content_html, meta_title, meta_description, category_name, tags

Fokus editorial:
- Utamakan Kalimantan Selatan, Kotabaru, Tanah Bumbu, kecamatan, dan desa-desa terkait.
- Cari angle yang lebih beragam: ekonomi, kriminal, kecelakaan, kebijakan, layanan publik, viral lokal, infrastruktur, cuaca, pendidikan, kesehatan, olahraga, komunitas.
- Hindari membuat semua artikel bertopik sama jika pool sumber beragam.

Kumpulan sumber untuk satu story draft:
{$sourceBrief}
PROMPT;
    }

    protected function normalizeJson(string $value): string
    {
        $value = trim($value);

        if (str_starts_with($value, '```')) {
            $value = preg_replace('/^```(?:json)?/i', '', $value) ?? $value;
            $value = preg_replace('/```$/', '', $value) ?? $value;
        }

        return trim($value);
    }
}
