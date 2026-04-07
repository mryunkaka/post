<?php

namespace App\Support;

class MediaPath
{
    public static function normalize(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $normalized = trim(str_replace('\\', '/', $path));

        if ($normalized === '') {
            return null;
        }

        $normalized = ltrim($normalized, '/');

        foreach ([
            'storage/app/public/',
            'app/public/',
            'public/',
            'storage/',
        ] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
                break;
            }
        }

        $normalized = ltrim($normalized, '/');

        return $normalized === '' ? null : $normalized;
    }

    public static function shareVariant(?string $path): ?string
    {
        $normalized = self::normalize($path);

        if ($normalized === null) {
            return null;
        }

        $extension = pathinfo($normalized, PATHINFO_EXTENSION);

        if ($extension === '') {
            return $normalized.'-share.jpg';
        }

        return substr($normalized, 0, -strlen($extension) - 1).'-share.jpg';
    }
}
