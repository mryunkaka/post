<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class BackupService
{
    public function __construct(
        protected Filesystem $files,
    ) {}

    public function backupDatabase(?CarbonInterface $timestamp = null): string
    {
        $timestamp ??= now();

        $connection = (string) config('database.default');

        if ($connection !== 'mysql') {
            throw new RuntimeException("Database backup only supports the mysql connection. Current connection: [{$connection}].");
        }

        $command = $this->databaseCommand($connection);
        $result = Process::timeout((int) config('backup.database.timeout', 300))->run($command);

        if (! $result->successful()) {
            throw new RuntimeException(trim($result->errorOutput()) ?: 'Database backup process failed.');
        }

        $path = $this->buildPath('database', $timestamp, 'sql');

        Storage::disk($this->backupDisk())->put($path, $result->output());
        $this->copyToRemoteDiskIfConfigured($path);

        return $path;
    }

    public function backupMedia(?CarbonInterface $timestamp = null): string
    {
        $timestamp ??= now();

        $path = $this->buildPath('media', $timestamp, 'zip');
        $backupDisk = Storage::disk($this->backupDisk());
        $mediaDisk = Storage::disk($this->mediaDisk());
        $archiveAbsolutePath = $backupDisk->path($path);

        $this->files->ensureDirectoryExists(dirname($archiveAbsolutePath));
        $this->files->delete($archiveAbsolutePath);

        $archive = new ZipArchive();

        if ($archive->open($archiveAbsolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create media backup archive.');
        }

        $mediaRoot = $mediaDisk->path('');
        $mediaFiles = $this->files->allFiles($mediaRoot);

        foreach ($mediaFiles as $file) {
            $absolutePath = $file->getPathname();
            $relativePath = ltrim(str_replace('\\', '/', substr($absolutePath, strlen($mediaRoot))), '/');

            $archive->addFile($absolutePath, $relativePath);
        }

        $archive->close();

        $this->copyToRemoteDiskIfConfigured($path);

        return $path;
    }

    public function prune(?CarbonInterface $now = null): array
    {
        $now ??= now();
        $deleted = [
            'database' => 0,
            'media' => 0,
        ];

        foreach (array_keys($deleted) as $type) {
            foreach (Storage::disk($this->backupDisk())->files($this->directoryFor($type)) as $path) {
                $lastModified = Storage::disk($this->backupDisk())->lastModified($path);

                if ($lastModified >= $now->copy()->subDays($this->retentionDays())->timestamp) {
                    continue;
                }

                Storage::disk($this->backupDisk())->delete($path);
                $deleted[$type]++;

                if ($this->remoteDisk()) {
                    Storage::disk($this->remoteDisk())->delete($path);
                }
            }
        }

        return $deleted;
    }

    public function retentionDays(): int
    {
        return max(1, (int) config('backup.retention_days', 7));
    }

    protected function databaseCommand(string $connection): array
    {
        $config = (array) config("database.connections.{$connection}");

        return array_filter([
            (string) config('backup.database.binary', 'mysqldump'),
            '--host='.$config['host'],
            '--port='.$config['port'],
            '--user='.$config['username'],
            '--password='.$config['password'],
            '--skip-comments',
            '--single-transaction',
            '--quick',
            $config['database'],
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    protected function buildPath(string $type, CarbonInterface $timestamp, string $extension): string
    {
        return $this->directoryFor($type).'/'.$timestamp->format('Y-m-d_His').'.'.$extension;
    }

    protected function directoryFor(string $type): string
    {
        return trim((string) config("backup.paths.{$type}", "backups/{$type}"), '/');
    }

    protected function backupDisk(): string
    {
        return (string) config('backup.disk', 'local');
    }

    protected function mediaDisk(): string
    {
        return (string) config('backup.media_disk', 'public');
    }

    protected function remoteDisk(): ?string
    {
        $disk = config('backup.remote_disk');

        return filled($disk) ? (string) $disk : null;
    }

    protected function copyToRemoteDiskIfConfigured(string $path): void
    {
        if (! $this->remoteDisk()) {
            return;
        }

        Storage::disk($this->remoteDisk())->put(
            $path,
            Storage::disk($this->backupDisk())->get($path),
        );
    }
}
