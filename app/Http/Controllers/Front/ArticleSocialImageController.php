<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\MediaPath;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;

class ArticleSocialImageController extends Controller
{
    public function __invoke(Article $article): Response
    {
        $path = MediaPath::normalize($article->featured_image);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return response()->file(public_path('og-default.png'), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        $image = Image::read(Storage::disk('public')->path($path))
            ->cover(1200, 630);

        return response((string) $image->encode(new JpegEncoder(quality: 86)), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
