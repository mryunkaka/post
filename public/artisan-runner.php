<?php

declare(strict_types=1);

/**
 * Temporary shared-hosting helper to run a small allowlist of Artisan commands
 * through /usr/bin/php when SSH access is unavailable.
 *
 * Usage:
 *   /artisan-runner.php?token=YOUR_TOKEN&cmd=about
 *   /artisan-runner.php?token=YOUR_TOKEN&cmd=optimize-clear
 *   /artisan-runner.php?token=YOUR_TOKEN&cmd=migrate-force
 *   /artisan-runner.php?token=YOUR_TOKEN&cmd=news-ingest&limit=10
 *   /artisan-runner.php?token=YOUR_TOKEN&cmd=news-generate-drafts&limit=10
 *   /artisan-runner.php?token=YOUR_TOKEN&cmd=schedule-run
 *
 * Required:
 *   Add ARTISAN_WEB_TOKEN=your-secret-token to the project .env file.
 *
 * Important:
 *   Delete this file after troubleshooting / deployment is finished.
 */

header('Content-Type: text/plain; charset=UTF-8');

$projectRoot = dirname(__DIR__);
$envPath = $projectRoot . DIRECTORY_SEPARATOR . '.env';
$artisanPath = $projectRoot . DIRECTORY_SEPARATOR . 'artisan';

if (!is_file($envPath)) {
    http_response_code(500);
    exit(".env file not found.\n");
}

if (!is_file($artisanPath)) {
    http_response_code(500);
    exit("artisan file not found.\n");
}

/**
 * Very small .env reader so this script works even when Laravel cannot boot.
 */
function readEnvValue(string $path, string $key): ?string
{
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

function detectPhpBinary(string $envPath): ?string
{
    $configured = readEnvValue($envPath, 'ARTISAN_PHP_BINARY');

    $candidates = array_filter([
        $configured,
        '/usr/local/bin/php',
        '/opt/cpanel/ea-php84/root/usr/bin/php',
        '/opt/cpanel/ea-php83/root/usr/bin/php',
        '/opt/cpanel/ea-php82/root/usr/bin/php',
        '/usr/bin/ea-php84',
        '/usr/bin/ea-php83',
        '/usr/bin/ea-php82',
        '/usr/bin/php',
    ]);

    foreach ($candidates as $candidate) {
        if (is_string($candidate) && $candidate !== '' && is_file($candidate) && is_executable($candidate)) {
            return $candidate;
        }
    }

    return null;
}

$phpBinary = detectPhpBinary($envPath);

if ($phpBinary === null) {
    http_response_code(500);
    exit("No executable PHP CLI binary found. Set ARTISAN_PHP_BINARY in .env.\n");
}

$expectedToken = readEnvValue($envPath, 'ARTISAN_WEB_TOKEN');
$providedToken = $_GET['token'] ?? '';

if ($expectedToken === null || $expectedToken === '') {
    http_response_code(500);
    exit("ARTISAN_WEB_TOKEN is missing in .env.\n");
}

if (!hash_equals($expectedToken, (string) $providedToken)) {
    http_response_code(403);
    exit("Invalid token.\n");
}

$allowedCommands = [
    'about' => 'about',
    'optimize-clear' => 'optimize:clear',
    'config-clear' => 'config:clear',
    'cache-clear' => 'cache:clear',
    'route-clear' => 'route:clear',
    'view-clear' => 'view:clear',
    'migrate-force' => 'migrate --force',
    'storage-link' => 'storage:link',
    'config-cache' => 'config:cache',
    'route-cache' => 'route:cache',
    'view-cache' => 'view:cache',
    'queue-restart' => 'queue:restart',
    'schedule-run' => 'schedule:run',
    'news-ingest' => 'news:ingest',
    'news-generate-drafts' => 'news:generate-drafts',
];

$cmdKey = (string) ($_GET['cmd'] ?? '');

if (!isset($allowedCommands[$cmdKey])) {
    http_response_code(400);
    echo "Unknown or disallowed cmd.\n\n";
    echo "Allowed commands:\n";

    foreach (array_keys($allowedCommands) as $key) {
        echo "- {$key}\n";
    }

    exit;
}

$artisanCommand = $allowedCommands[$cmdKey];

if ($cmdKey === 'news-ingest' || $cmdKey === 'news-generate-drafts') {
    $limit = $_GET['limit'] ?? null;

    if ($limit !== null && $limit !== '') {
        if (!ctype_digit((string) $limit) || (int) $limit < 1 || (int) $limit > 50) {
            http_response_code(400);
            exit("Invalid limit. Allowed range: 1-50.\n");
        }

        $artisanCommand .= ' --limit='.(int) $limit;
    }
}

if ($cmdKey === 'news-ingest') {
    $source = $_GET['source'] ?? null;

    if ($source !== null && $source !== '') {
        if (!preg_match('/^[a-z0-9_-]+$/i', (string) $source)) {
            http_response_code(400);
            exit("Invalid source.\n");
        }

        $artisanCommand .= ' --source='.escapeshellarg((string) $source);
    }
}

$shellCommand = sprintf(
    'cd %s && %s %s %s 2>&1',
    escapeshellarg($projectRoot),
    escapeshellarg($phpBinary),
    escapeshellarg($artisanPath),
    $artisanCommand
);

echo "Project root: {$projectRoot}\n";
echo "PHP binary: {$phpBinary}\n";
echo "Running: php artisan {$artisanCommand}\n";
echo str_repeat('-', 72) . "\n";

$output = [];
$exitCode = 0;
exec($shellCommand, $output, $exitCode);

echo implode("\n", $output) . "\n";
echo str_repeat('-', 72) . "\n";
echo "Exit code: {$exitCode}\n";
