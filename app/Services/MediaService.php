<?php

namespace App\Services;

use App\Support\MediaPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class MediaService
{
    protected const FEATURED_IMAGE_TARGET_BYTES = 2_000_000;
    protected const FEATURED_SHARE_TARGET_BYTES = 700_000;

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
        $sharePath = MediaPath::shareVariant($path);

        $image = Image::decode($file)->orient();
        $encodedImage = $this->encodeFeaturedImage(clone $image);
        $encodedShareImage = $this->encodeFeaturedShareImage(clone $image);

        Storage::disk('public')->put($path, $encodedImage);
        if ($sharePath !== null) {
            Storage::disk('public')->put($sharePath, $encodedShareImage);
        }

        $this->optimize($path);
        if ($sharePath !== null) {
            $this->optimize($sharePath);
        }

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

    protected function encodeFeaturedShareImage(ImageInterface $image): string
    {
        $bestAttempt = null;
        $candidateImage = $image->cover(1200, 630);

        foreach ([84, 80, 76, 72, 68, 64, 60, 56] as $quality) {
            $encoded = (string) $candidateImage->encode(new JpegEncoder(quality: $quality));
            $bestAttempt = $encoded;

            if (strlen($encoded) <= self::FEATURED_SHARE_TARGET_BYTES) {
                return $encoded;
            }
        }

        return $bestAttempt
            ?? (string) $candidateImage->encode(new JpegEncoder(quality: 56));
    }

    public function deletePublicFile(?string $path): void
    {
        if (MediaPath::isExternalUrl($path)) {
            return;
        }

        $path = MediaPath::normalize($path);
        $sharePath = MediaPath::shareVariant($path);

        if ($path === null) {
            return;
        }

        Storage::disk('public')->delete($path);
        if ($sharePath !== null) {
            Storage::disk('public')->delete($sharePath);
        }
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
