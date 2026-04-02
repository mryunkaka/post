<?php

use App\Services\ArticleViewBufferService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('articles:flush-view-counts', function () {
    $flushedRows = app(ArticleViewBufferService::class)->flush();

    $this->info("Flushed view counters for {$flushedRows} article(s).");
})->purpose('Flush buffered public article views into the database');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('articles:flush-view-counts')->everyTenMinutes();
