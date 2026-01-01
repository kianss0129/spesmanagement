<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Running debug-broker.php\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

$email = $argv[1] ?? 'reset-test@example.com';

echo "Running debug-broker.php for: $email\n";

try {
    $broker = Password::broker('users');
    echo 'Broker is ' . (is_object($broker) ? get_class($broker) : var_export($broker,true)) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Broker exception: ' . $e->getMessage() . PHP_EOL;
}

$user = \App\Models\User::where('email', $email)->first();
echo "User found: " . ($user ? $user->id : 'no') . "\n";

$queries = [];
DB::listen(function ($q) use (&$queries) {
    $queries[] = ['sql' => $q->sql, 'bindings' => $q->bindings, 'time' => $q->time];
});

try {
    $token = $broker->createToken($user);
    echo "createToken returned token length=" . strlen($token) . "\n";
} catch (\Throwable $e) {
    echo "createToken threw: " . $e->getMessage() . "\n";
}

try {
    $status = Password::sendResetLink(['email' => $email]);
    echo "sendResetLink status: " . var_export($status, true) . "\n";
} catch (\Throwable $e) {
    echo "sendResetLink threw: " . $e->getMessage() . "\n";
}

echo "DB queries observed: " . json_encode($queries) . "\n";

$rows1 = DB::table('password_reset_tokens')->where('email', $email)->get();
$rows2 = DB::table('password_resets')->where('email', $email)->get();

echo "password_reset_tokens count: " . $rows1->count() . "\n";
echo "password_resets count: " . $rows2->count() . "\n";
