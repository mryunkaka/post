<?php

declare(strict_types=1);

$repo = '/home/hark8423/public_html/post';
$branch = 'main';
$remote = 'origin';
$gitBinary = '/usr/bin/git';
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

if (! is_file($gitBinary) || ! is_executable($gitBinary)) {
    $message = "Deploy gagal: git binary tidak ditemukan di {$gitBinary}";
    echo $message."\n";
    $writeLog(date('Y-m-d H:i:s').' - '.$message);
    exit(1);
}

chdir($repo);

putenv('HOME=/home/hark8423');
putenv('PATH=/usr/local/bin:/usr/bin:/bin');
putenv('GIT_TERMINAL_PROMPT=0');
putenv('LANG=en_US.UTF-8');

$run = static function (string $command): string {
    return trim((string) shell_exec($command.' 2>&1'));
};

$date = date('Y-m-d H:i:s');
$old = $run(sprintf('%s rev-parse HEAD', escapeshellcmd($gitBinary)));
$output = $run(sprintf(
    '%s -C %s pull %s %s',
    escapeshellcmd($gitBinary),
    escapeshellarg($repo),
    escapeshellarg($remote),
    escapeshellarg($branch)
));
$new = $run(sprintf('%s rev-parse HEAD', escapeshellcmd($gitBinary)));

echo "Menjalankan deploy POST...\n\n";
echo "Repo: {$repo}\n";
echo "Git binary: {$gitBinary}\n";
echo "Commit lama: {$old}\n";
echo "Commit baru: {$new}\n\n";
echo "Output git pull:\n{$output}\n\n";

if ($old !== $new && $old !== '' && $new !== '') {
    $commits = $run(sprintf(
        '%s -C %s log %s..%s --pretty=format:"%%h | %%an | %%s"',
        escapeshellcmd($gitBinary),
        escapeshellarg($repo),
        escapeshellarg($old),
        escapeshellarg($new)
    ));

    foreach (explode("\n", trim($commits)) as $commit) {
        if ($commit !== '') {
            $writeLog("{$date} - Deploy {$commit}");
        }
    }

    echo "Status: Deploy berhasil (ada perubahan)\n";
} else {
    echo "Status: Tidak ada perubahan\n";
}

echo "\nLog: {$log}\n";
