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
}
