<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

$projectRoot = dirname(__DIR__);

require $projectRoot.'/vendor/autoload.php';

$app = require $projectRoot.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$token = $_GET['token'] ?? '';
$expectedToken = env('HOSTING_CHECK_TOKEN') ?: env('ARTISAN_WEB_TOKEN');

if ($expectedToken && ! hash_equals((string) $expectedToken, (string) $token)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Forbidden\n";
    exit;
}

$email = (string) ($_GET['email'] ?? 'admin@local.test');
$plain = (string) ($_GET['password'] ?? 'password');

header('Content-Type: text/plain; charset=UTF-8');

echo "=== Auth Debug ===\n";
echo 'Email: '.$email."\n";

$user = User::query()->where('email', $email)->first();

if (! $user) {
    echo "User found: NO\n";
    exit;
}

echo "User found: YES\n";
echo 'User ID: '.$user->id."\n";
echo 'Role: '.$user->role."\n";
echo 'is_active: '.((string) $user->is_active)."\n";
echo 'Hash length: '.strlen((string) $user->password)."\n";
echo 'Hash prefix: '.substr((string) $user->password, 0, 20)."...\n";
echo 'Hash::check(password, hash): '.(Hash::check($plain, (string) $user->password) ? 'YES' : 'NO')."\n";
echo 'password_verify(password, hash): '.(password_verify($plain, (string) $user->password) ? 'YES' : 'NO')."\n";

$attempt = auth()->attempt([
    'email' => $email,
    'password' => $plain,
]);

echo 'Auth::attempt(...): '.($attempt ? 'YES' : 'NO')."\n";

if ($attempt) {
    auth()->logout();
}

echo "=== Done ===\n";
