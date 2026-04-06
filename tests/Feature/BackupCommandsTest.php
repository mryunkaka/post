<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupCommandsTest extends TestCase
{
    public function test_database_backup_command_creates_sql_file_on_backup_disk(): void
    {
        Storage::fake('local');

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => 3306,
            'database.connections.mysql.database' => 'media',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => 'secret',
            'backup.disk' => 'local',
            'backup.remote_disk' => null,
        ]);

        Process::fake([
            '*' => Process::result('-- sample sql dump --'),
        ]);

        $this->artisan('backup:database')
            ->assertSuccessful();

        $files = Storage::disk('local')->files('backups/database');

        $this->assertCount(1, $files);
        $this->assertStringContainsString('.sql', $files[0]);
        $this->assertStringContainsString('-- sample sql dump --', Storage::disk('local')->get($files[0]));
    }

    public function test_media_backup_command_creates_zip_archive(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        config([
            'backup.disk' => 'local',
            'backup.media_disk' => 'public',
            'backup.remote_disk' => null,
        ]);

        Storage::disk('public')->put('articles/example.webp', 'image-content');
        Storage::disk('public')->put('ads/banner.txt', 'banner-content');

        $this->artisan('backup:media')
            ->assertSuccessful();

        $files = Storage::disk('local')->files('backups/media');

        $this->assertCount(1, $files);
        $this->assertStringContainsString('.zip', $files[0]);

        $archive = new ZipArchive();
        $opened = $archive->open(Storage::disk('local')->path($files[0]));

        $this->assertTrue($opened === true);
        $this->assertNotFalse($archive->locateName('articles/example.webp'));
        $this->assertNotFalse($archive->locateName('ads/banner.txt'));
        $archive->close();
    }

    public function test_prune_command_deletes_backups_older_than_retention_window(): void
    {
        Storage::fake('local');

        config([
            'backup.disk' => 'local',
            'backup.remote_disk' => null,
            'backup.retention_days' => 7,
        ]);

        Storage::disk('local')->put('backups/database/old.sql', 'old');
        Storage::disk('local')->put('backups/database/new.sql', 'new');
        Storage::disk('local')->put('backups/media/old.zip', 'old');
        Storage::disk('local')->put('backups/media/new.zip', 'new');

        touch(Storage::disk('local')->path('backups/database/old.sql'), now()->subDays(8)->timestamp);
        touch(Storage::disk('local')->path('backups/database/new.sql'), now()->subDays(2)->timestamp);
        touch(Storage::disk('local')->path('backups/media/old.zip'), now()->subDays(8)->timestamp);
        touch(Storage::disk('local')->path('backups/media/new.zip'), now()->subDays(2)->timestamp);

        $this->artisan('backup:prune')
            ->assertSuccessful();

        Storage::disk('local')->assertMissing('backups/database/old.sql');
        Storage::disk('local')->assertMissing('backups/media/old.zip');
        Storage::disk('local')->assertExists('backups/database/new.sql');
        Storage::disk('local')->assertExists('backups/media/new.zip');
    }
}
