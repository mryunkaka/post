<?php

namespace Tests\Unit;

use App\Services\SourceRegistryService;
use Tests\TestCase;

class SourceRegistryServiceTest extends TestCase
{
    public function test_it_returns_active_editorial_sources(): void
    {
        $sources = app(SourceRegistryService::class)->active();

        $this->assertGreaterThanOrEqual(4, $sources->count());
        $this->assertTrue($sources->every(fn (object $source) => $source->active === true));
    }

    public function test_it_can_filter_sources_by_region(): void
    {
        $sources = app(SourceRegistryService::class)->forRegion('kotabaru');

        $this->assertTrue($sources->isNotEmpty());
        $this->assertTrue($sources->every(fn (object $source) => $source->region === 'kotabaru'));
    }
}
