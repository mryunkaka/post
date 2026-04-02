<?php

declare(strict_types=1);

$deployPath = '/home/hark8423/public_html/post';
$branch = 'main';
$remote = 'origin';
$logFile = '/home/hark8423/git-deploy-post.log';
$lockFile = '/home/hark8423/git-deploy-post.lock';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Forbidden\n";
    exit;
}

fwrite(STDOUT, "Menjalankan deploy POST...\n\n");

if (! is_dir($deployPath) || ! is_dir($deployPath.'/.git')) {
    fwrite(STDOUT, "Deploy gagal: folder atau repo tidak ditemukan\n");
    exit(1);
}

$lockHandle = fopen($lockFile, 'c+');

if ($lockHandle === false || ! flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDOUT, "Deploy dibatalkan: proses deploy lain masih berjalan\n");
    exit(1);
}

@set_time_limit(180);
chdir($deployPath);

$run = static function (string $command): string {
    return trim((string) shell_exec($command.' 2>&1'));
};

$gitDir = escapeshellarg($deployPath.'/.git');
$workTree = escapeshellarg($deployPath);
$git = static fn (string $command): string => trim((string) shell_exec(
    sprintf('git --git-dir=%s --work-tree=%s %s 2>&1', $gitDir, $workTree, $command)
));

$git('config core.compression 0');
$git('config pack.threads 1');
$git('config http.postBuffer 524288000');

$oldCommit = $git('rev-parse HEAD');
fwrite(STDOUT, "Commit lama: {$oldCommit}\n");

$git('reset --hard HEAD');
$git("clean -fd -e .env -e storage -e vendor -e public/storage -e public/uploads");

$fetchOutput = '';
$resetOutput = '';

for ($i = 0; $i < 3; $i++) {
    $fetchOutput = $git(sprintf(
        'fetch --depth=1 %s %s',
        escapeshellarg($remote),
        escapeshellarg($branch)
    ));

    if (stripos($fetchOutput, 'fatal') === false && stripos($fetchOutput, 'error:') === false) {
        $resetOutput = $git(sprintf(
            'reset --hard %s/%s',
            escapeshellarg($remote),
            escapeshellarg($branch)
        ));
        break;
    }

    sleep(2);
}

if ($resetOutput === '') {
    fwrite(STDOUT, "Fetch gagal:\n{$fetchOutput}\n");
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
    exit(1);
}

$newCommit = $git('rev-parse HEAD');
fwrite(STDOUT, "Commit baru: {$newCommit}\n\n");

if ($oldCommit !== $newCommit) {
    $commits = $git("log {$oldCommit}..{$newCommit} --pretty=format:'%h | %an | %s'");
    $date = date('Y-m-d H:i:s');

    foreach (explode("\n", trim($commits)) as $commit) {
        if ($commit !== '') {
            file_put_contents($logFile, "{$date} - Deploy {$commit}\n", FILE_APPEND);
        }
    }

    fwrite(STDOUT, "Status: Deploy berhasil (ada perubahan)\n");
} else {
    fwrite(STDOUT, "Status: Tidak ada perubahan\n");
}

$runtimeDirs = [
    $deployPath.'/storage',
    $deployPath.'/storage/app/public',
    $deployPath.'/storage/framework/cache/data',
    $deployPath.'/storage/framework/sessions',
    $deployPath.'/storage/framework/views',
    $deployPath.'/storage/logs',
    $deployPath.'/bootstrap/cache',
];

foreach ($runtimeDirs as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$notes = [];

if (! is_dir($deployPath.'/vendor')) {
    $notes[] = 'vendor belum ada. Upload manual vendor.zip sebelum aplikasi dijalankan.';
}

if (! file_exists($deployPath.'/public/build/manifest.json')) {
    $notes[] = 'public/build/manifest.json belum ada. Pastikan hasil npm build ikut terdeploy.';
}

fwrite(STDOUT, "\nSelesai.\n");
fwrite(STDOUT, "Log: {$logFile}\n");

if ($notes !== []) {
    fwrite(STDOUT, "\nCatatan:\n- ".implode("\n- ", $notes)."\n");
}

flock($lockHandle, LOCK_UN);
fclose($lockHandle);

