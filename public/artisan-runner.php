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
$phpBinary = '/usr/bin/php';

if (!is_file($envPath)) {
    http_response_code(500);
    exit(".env file not found.\n");
}

if (!is_file($artisanPath)) {
    http_response_code(500);
    exit("artisan file not found.\n");
}

if (!is_executable($phpBinary)) {
    http_response_code(500);
    exit("/usr/bin/php is not executable or not available.\n");
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
$shellCommand = sprintf(
    'cd %s && %s artisan %s 2>&1',
    escapeshellarg($projectRoot),
    escapeshellarg($phpBinary),
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
