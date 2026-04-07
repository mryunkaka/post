<?php

namespace App\Support;

class MediaPath
{
    public static function isExternalUrl(?string $path): bool
    {
        if ($path === null) {
            return false;
        }

        $normalized = trim($path);

        if ($normalized === '') {
            return false;
        }

        return filter_var($normalized, FILTER_VALIDATE_URL) !== false;
    }

    public static function normalize(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        if (self::isExternalUrl($path)) {
            return trim($path);
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
        if (self::isExternalUrl($path)) {
            return null;
        }

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
