<?php

declare(strict_types=1);

$repo = '/home/hark8423/public_html/post';
$branch = 'main';
$remote = 'origin';
$log = '/home/hark8423/git-deploy-post.log';

header('Content-Type: text/plain; charset=UTF-8');
@set_time_limit(120);

$writeLog = static function (string $message) use ($log): void {
    file_put_contents($log, $message.PHP_EOL, FILE_APPEND);
};

if (! is_dir($repo) || ! is_dir($repo.'/.git')) {
    $message = 'Deploy gagal: folder repo tidak ditemukan';
    echo $message."\n";
    $writeLog(date('Y-m-d H:i:s').' - '.$message);
    exit(1);
}

chdir($repo);

$run = static function (string $command): string {
    return trim((string) shell_exec($command.' 2>&1'));
};

$date = date('Y-m-d H:i:s');
$old = $run('git rev-parse HEAD');
$output = $run(sprintf('git pull %s %s', escapeshellarg($remote), escapeshellarg($branch)));
$new = $run('git rev-parse HEAD');

echo "Menjalankan deploy POST...\n\n";
echo "Repo: {$repo}\n";
echo "Commit lama: {$old}\n";
echo "Commit baru: {$new}\n\n";
echo "Output git pull:\n{$output}\n\n";

$writeLog("{$date} - START deploy {$old}");
$writeLog("{$date} - git pull output: {$output}");

if ($old !== $new && $old !== '' && $new !== '') {
    $commits = $run("git log {$old}..{$new} --pretty=format:'%h | %an | %s'");

    foreach (explode("\n", trim($commits)) as $commit) {
        if ($commit !== '') {
            $writeLog("{$date} - Deploy {$commit}");
        }
    }

    echo "Status: Deploy berhasil (ada perubahan)\n";
    $writeLog("{$date} - Status: Deploy berhasil (ada perubahan)");
} else {
    echo "Status: Tidak ada perubahan\n";
    $writeLog("{$date} - Status: Tidak ada perubahan");
}

echo "\nLog: {$log}\n";

