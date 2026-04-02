<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaRouteTest extends TestCase
{
    public function test_public_media_route_serves_file_from_public_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/test.webp', 'fake-image-content');

        $response = $this->get(route('media.public', ['path' => 'articles/test.webp']));

        $response->assertOk();
        $this->assertStringContainsString('public', (string) $response->headers->get('cache-control'));
        $this->assertStringContainsString('max-age=86400', (string) $response->headers->get('cache-control'));
    }

    public function test_public_media_route_rejects_path_traversal(): void
    {
        $this->get(route('media.public', ['path' => '../secret.txt']))
            ->assertNotFound();
    }
}
