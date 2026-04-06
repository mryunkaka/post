<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),

    'remote_disk' => env('BACKUP_REMOTE_DISK'),

    'media_disk' => env('BACKUP_MEDIA_DISK', 'public'),

    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 7),

    'database' => [
        'binary' => env('BACKUP_DATABASE_BINARY', 'mysqldump'),
        'timeout' => (int) env('BACKUP_DATABASE_TIMEOUT', 300),
    ],

    'paths' => [
        'database' => env('BACKUP_DATABASE_PATH', 'backups/database'),
        'media' => env('BACKUP_MEDIA_PATH', 'backups/media'),
    ],

    'schedule' => [
        'database' => env('BACKUP_DATABASE_SCHEDULE', '02:00'),
        'media' => env('BACKUP_MEDIA_SCHEDULE', '02:30'),
        'prune' => env('BACKUP_PRUNE_SCHEDULE', '03:00'),
    ],
];
