<?php

use App\Services\ArticleService;
use App\Services\ArticleViewBufferService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('articles:flush-view-counts', function () {
    $flushedRows = app(ArticleViewBufferService::class)->flush();

    $this->info("Flushed view counters for {$flushedRows} article(s).");
})->purpose('Flush buffered public article views into the database');

Artisan::command('articles:publish-scheduled', function () {
    $publishedCount = app(ArticleService::class)->publishDueArticles();

    $this->info("Published {$publishedCount} scheduled article(s).");
})->purpose('Publish scheduled articles whose publish time has arrived');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('articles:flush-view-counts')->everyTenMinutes();
Schedule::command('articles:publish-scheduled')->everyMinute();
