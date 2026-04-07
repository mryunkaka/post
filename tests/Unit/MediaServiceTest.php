<?php

namespace Tests\Unit;

use App\Services\MediaService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use WithFaker;

    public function test_it_stores_article_featured_image_as_webp_on_public_disk(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('featured.jpg', 1800, 1200);
        $path = app(MediaService::class)->storeArticleFeaturedImage($file);
        $sharePath = \App\Support\MediaPath::shareVariant($path);

        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertNotNull($sharePath);
        Storage::disk('public')->assertExists($sharePath);
    }
}
