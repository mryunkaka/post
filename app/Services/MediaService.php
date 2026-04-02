<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class MediaService
{
    public function storeArticleFeaturedImage(UploadedFile $file, ?string $oldPath = null): string
    {
        $directory = 'articles/'.now()->format('Y/m');
        $filename = (string) Str::ulid().'.webp';
        $path = $directory.'/'.$filename;

        $image = Image::decode($file)
            ->orient()
            ->scaleDown(width: 1200, height: 1200);

        Storage::disk('public')->put(
            $path,
            (string) $image->encode(new WebpEncoder(quality: 82))
        );

        $this->optimize($path);

        if ($oldPath !== null && $oldPath !== $path) {
            $this->deletePublicFile($oldPath);
        }

        return $path;
    }

    public function deletePublicFile(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    protected function optimize(string $path): void
    {
        $fullPath = Storage::disk('public')->path($path);

        if (! is_file($fullPath)) {
            return;
        }

        try {
            ImageOptimizer::optimize($fullPath);
        } catch (\Throwable) {
            // Shared hosting may not provide image optimizer binaries.
        }
    }
}
