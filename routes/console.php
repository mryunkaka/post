<?php

use App\Services\ArticleService;
use App\Services\ArticleViewBufferService;
use App\Services\BackupService;
use App\Services\NewsDraftGenerationService;
use App\Services\NewsIngestionService;
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

Artisan::command('backup:database', function () {
    $path = app(BackupService::class)->backupDatabase();

    $this->info("Database backup created at {$path}.");
})->purpose('Create a database backup using mysqldump');

Artisan::command('backup:media', function () {
    $path = app(BackupService::class)->backupMedia();

    $this->info("Media backup created at {$path}.");
})->purpose('Create a zip archive backup of the public media disk');

Artisan::command('backup:prune', function () {
    $deleted = app(BackupService::class)->prune();

    $this->info("Pruned {$deleted['database']} database backup(s) and {$deleted['media']} media backup(s).");
})->purpose('Delete expired backup files based on retention rules');

Artisan::command('news:ingest {--source=} {--limit=}', function () {
    $source = $this->option('source');
    $limit = $this->option('limit');
    $parsedLimit = is_numeric($limit) ? (int) $limit : null;

    $summary = app(NewsIngestionService::class)->ingest(
        $source !== null && $source !== '' ? (string) $source : null,
        $parsedLimit,
    );

    $this->info("Processed {$summary['sources']} source(s).");
    $this->info("Fetched {$summary['fetched']} item(s), stored {$summary['stored']}, validated {$summary['validated']}, rejected {$summary['rejected']}.");
})->purpose('Ingest candidate news items from configured AI editorial sources');

Artisan::command('news:generate-drafts {--limit=}', function () {
    $limit = $this->option('limit');
    $parsedLimit = is_numeric($limit) ? (int) $limit : null;

    $summary = app(NewsDraftGenerationService::class)->generatePendingDrafts($parsedLimit);

    $this->info("Processed {$summary['processed']} validated candidate(s).");
    $this->info("Drafted {$summary['drafted']} article(s), failed {$summary['failed']} candidate(s).");
})->purpose('Generate article drafts from validated AI news candidates');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('articles:flush-view-counts')->everyTenMinutes();
Schedule::command('articles:publish-scheduled')->everyMinute();
Schedule::command('backup:database')->dailyAt((string) config('backup.schedule.database', '02:00'));
Schedule::command('backup:media')->dailyAt((string) config('backup.schedule.media', '02:30'));
Schedule::command('backup:prune')->dailyAt((string) config('backup.schedule.prune', '03:00'));
Schedule::command('news:ingest --limit='.((int) config('ai_editorial.ingest.default_limit', 10)))
    ->everyThirtyMinutes()
    ->when(fn (): bool => (bool) config('ai_editorial.ingest_enabled', false));
Schedule::command('news:generate-drafts --limit='.((int) config('ai_editorial.generation.default_limit', 10)))
    ->hourly()
    ->when(fn (): bool => (bool) config('ai_editorial.enabled', false));
