<?php

use Spatie\ImageOptimizer\Optimizers\Cwebp;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Svgo;

return [
    'optimizers' => [
        Jpegoptim::class => [
            '-m82',
            '--strip-all',
            '--all-progressive',
        ],

        Pngquant::class => [
            '--force',
            '--strip',
            '--quality=70-85',
        ],

        Optipng::class => [
            '-i0',
            '-o2',
            '-quiet',
        ],

        Svgo::class => [
            '--disable=cleanupIDs',
        ],

        Gifsicle::class => [
            '-b',
            '-O3',
        ],

        Cwebp::class => [
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 82',
        ],
    ],

    // Shared hosting often lacks globally installed binaries.
    // Set this in .env or per-server config if the host exposes a custom binaries path.
    'binary_path' => env('IMAGE_OPTIMIZER_BINARY_PATH', ''),

    'timeout' => 60,

    'log_optimizer_activity' => env('IMAGE_OPTIMIZER_LOG_ACTIVITY', false),
];
