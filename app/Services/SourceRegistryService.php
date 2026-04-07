<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SourceRegistryService
{
    public function all(): Collection
    {
        return collect(config('ai_editorial.sources', []))
            ->map(fn (array $source) => (object) $source)
            ->values();
    }

    public function active(): Collection
    {
        return $this->all()
            ->filter(fn (object $source) => (bool) ($source->active ?? false))
            ->values();
    }

    public function forRegion(string $region): Collection
    {
        return $this->active()
            ->filter(fn (object $source) => ($source->region ?? null) === $region)
            ->values();
    }
}
