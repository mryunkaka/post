<?php

namespace App\Services;

use App\Support\MediaPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class MediaService
{
    protected const FEATURED_IMAGE_TARGET_BYTES = 2_000_000;

    /**
     * @var list<int>
     */
    protected const FEATURED_IMAGE_WIDTH_STEPS = [2200, 2000, 1800, 1600, 1400, 1200];

    /**
     * @var list<int>
     */
    protected const FEATURED_IMAGE_QUALITY_STEPS = [86, 82, 78, 74, 70, 66, 62, 58, 54, 50];

    public function storeArticleFeaturedImage(UploadedFile $file, ?string $oldPath = null): string
    {
        $directory = 'articles/'.now()->format('Y/m');
        $filename = (string) Str::ulid().'.webp';
        $path = $directory.'/'.$filename;

        $image = Image::decode($file)->orient();
        $encodedImage = $this->encodeFeaturedImage($image);

        Storage::disk('public')->put($path, $encodedImage);

        $this->optimize($path);

        if ($oldPath !== null && $oldPath !== $path) {
            $this->deletePublicFile($oldPath);
        }

        return $path;
    }

    protected function encodeFeaturedImage(ImageInterface $image): string
    {
        $bestAttempt = null;

        foreach (self::FEATURED_IMAGE_WIDTH_STEPS as $maxWidth) {
            $candidateImage = clone $image;
            $candidateImage = $candidateImage->scaleDown(width: $maxWidth, height: $maxWidth);

            foreach (self::FEATURED_IMAGE_QUALITY_STEPS as $quality) {
                $encoded = (string) $candidateImage->encode(new WebpEncoder(quality: $quality));
                $bestAttempt = $encoded;

                if (strlen($encoded) <= self::FEATURED_IMAGE_TARGET_BYTES) {
                    return $encoded;
                }
            }
        }

        return $bestAttempt
            ?? (string) $image
                ->scaleDown(width: 1200, height: 1200)
                ->encode(new WebpEncoder(quality: 50));
    }

    public function deletePublicFile(?string $path): void
    {
        $path = MediaPath::normalize($path);

        if ($path === null) {
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
