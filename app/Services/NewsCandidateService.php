<?php

namespace App\Services;

use App\Models\NewsCandidate;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NewsCandidateService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function upsertFromIngestion(array $payload): NewsCandidate
    {
        $sourceUrl = trim((string) Arr::get($payload, 'source_url', ''));
        $sourceUrlHash = hash('sha256', Str::lower($sourceUrl));

        $candidate = NewsCandidate::query()->firstOrNew([
            'source_url_hash' => $sourceUrlHash,
        ]);

        $candidate->fill([
            'source_code' => trim((string) Arr::get($payload, 'source_code', '')),
            'source_name' => trim((string) Arr::get($payload, 'source_name', '')),
            'source_url' => $sourceUrl,
            'source_url_hash' => $sourceUrlHash,
            'source_published_at' => $this->nullableDateTime(Arr::get($payload, 'source_published_at')),
            'region' => $this->nullableString(Arr::get($payload, 'region')),
            'title' => trim((string) Arr::get($payload, 'title', '')),
            'excerpt' => $this->nullableString(Arr::get($payload, 'excerpt')),
            'image_url' => $this->nullableString(Arr::get($payload, 'image_url')),
            'facts_summary' => $this->nullableString(Arr::get($payload, 'facts_summary')),
            'raw_payload' => Arr::get($payload, 'raw_payload'),
            'status' => Arr::get($payload, 'status', $candidate->exists ? $candidate->status : 'pending'),
            'rejection_reason' => $this->nullableString(Arr::get($payload, 'rejection_reason')),
            'article_id' => Arr::get($payload, 'article_id'),
        ]);
        $candidate->save();

        return $candidate;
    }

    protected function nullableDateTime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse((string) $value);
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
