<?php

declare(strict_types=1);

/**
 * Temporary hosting diagnostic page for shared-hosting Laravel deployments.
 *
 * Delete this file after debugging is complete.
 *
 * Optional:
 *   Add HOSTING_CHECK_TOKEN=your-secret-token to .env
 *   Then open:
 *   /hosting-check.php?token=your-secret-token
 */

header('Content-Type: text/plain; charset=UTF-8');

$projectRoot = dirname(__DIR__);
$publicRoot = __DIR__;
$envPath = $projectRoot . DIRECTORY_SEPARATOR . '.env';
$autoloadPath = $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$bootstrapPath = $projectRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php';
$storagePath = $projectRoot . DIRECTORY_SEPARATOR . 'storage';
$bootstrapCachePath = $projectRoot . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache';
$manifestPath = $publicRoot . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'manifest.json';

function line(string $label, string $value): void
{
    echo str_pad($label, 32) . ': ' . $value . PHP_EOL;
}

function boolText(bool $value): string
{
    return $value ? 'YES' : 'NO';
}

function envValue(string $path, string $key): ?string
{
    $lines = @file($path, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        return null;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_starts_with($line, $key . '=')) {
            continue;
        }

        $value = substr($line, strlen($key) + 1);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        return $value;
    }

    return null;
}

if (is_file($envPath)) {
    $expectedToken = envValue($envPath, 'HOSTING_CHECK_TOKEN');
    $providedToken = $_GET['token'] ?? '';

    if ($expectedToken !== null && $expectedToken !== '') {
        if (!hash_equals($expectedToken, (string) $providedToken)) {
            http_response_code(403);
            exit("Invalid token.\n");
        }
    }
}

echo "=== Hosting Check ===" . PHP_EOL;
line('Timestamp', date('c'));
line('Project root', $projectRoot);
line('Public root', $publicRoot);
line('PHP version', PHP_VERSION);
line('SAPI', PHP_SAPI);
line('OS', PHP_OS_FAMILY);
echo PHP_EOL;

echo "=== Required Paths ===" . PHP_EOL;
line('.env exists', boolText(is_file($envPath)));
line('vendor/autoload.php exists', boolText(is_file($autoloadPath)));
line('bootstrap/app.php exists', boolText(is_file($bootstrapPath)));
line('public/build/manifest.json exists', boolText(is_file($manifestPath)));
line('storage exists', boolText(is_dir($storagePath)));
line('bootstrap/cache exists', boolText(is_dir($bootstrapCachePath)));
echo PHP_EOL;

echo "=== Permissions ===" . PHP_EOL;
line('storage writable', boolText(is_writable($storagePath)));
line('bootstrap/cache writable', boolText(is_writable($bootstrapCachePath)));
line('public writable', boolText(is_writable($publicRoot)));
echo PHP_EOL;

echo "=== PHP Extensions ===" . PHP_EOL;
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'gd', 'imagick'];
foreach ($extensions as $extension) {
    line($extension, boolText(extension_loaded($extension)));
}
echo PHP_EOL;

echo "=== Env Snapshot ===" . PHP_EOL;
$envKeys = [
    'APP_NAME',
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'APP_KEY',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'SESSION_DRIVER',
    'CACHE_STORE',
    'QUEUE_CONNECTION',
    'FILESYSTEM_DISK',
];

foreach ($envKeys as $key) {
    $value = envValue($envPath, $key);

    if ($key === 'APP_KEY' && $value !== null && $value !== '') {
        $value = 'SET';
    }

    line($key, $value === null || $value === '' ? '(empty)' : $value);
}
echo PHP_EOL;

echo "=== Laravel Bootstrap Test ===" . PHP_EOL;

try {
    if (!is_file($autoloadPath)) {
        throw new RuntimeException('vendor/autoload.php missing');
    }

    if (!is_file($bootstrapPath)) {
        throw new RuntimeException('bootstrap/app.php missing');
    }

    require $autoloadPath;
    $app = require $bootstrapPath;

    line('Bootstrap loaded', 'YES');
    line('Container class', get_class($app));

    try {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        line('Console kernel bootstrap', 'YES');

        try {
            $db = $app->make('db');
            $pdo = $db->connection()->getPdo();
            line('Database connection', 'YES');
            line('DB server version', $pdo->getAttribute(PDO::ATTR_SERVER_VERSION));
        } catch (Throwable $dbError) {
            line('Database connection', 'FAILED');
            echo $dbError::class . ': ' . $dbError->getMessage() . PHP_EOL;
        }
    } catch (Throwable $kernelError) {
        line('Console kernel bootstrap', 'FAILED');
        echo $kernelError::class . ': ' . $kernelError->getMessage() . PHP_EOL;
    }
} catch (Throwable $e) {
    line('Bootstrap loaded', 'FAILED');
    echo $e::class . ': ' . $e->getMessage() . PHP_EOL;
    echo PHP_EOL . '--- Trace ---' . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL;
echo "=== Done ===" . PHP_EOL;
