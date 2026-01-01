<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Running debug-send-reset.php\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'reset-test@example.com';

echo "Email: $email\n";

$broker = config('auth.defaults.passwords', 'users');
$brokerConfig = config("auth.passwords.$broker", []);
$table = $brokerConfig['table'] ?? 'password_resets';

echo "Broker: $broker\n";
echo "Configured table: $table\n";

// Ensure user exists
$user = App\Models\User::where('email', $email)->first();
if (! $user) {
    echo "User not found. Creating test user...\n";
    $user = App\Models\User::factory()->create(['email' => $email]);
}

$userExists = (bool) $user;
echo "User exists: ".($userExists ? 'yes' : 'no')." (id={$user->id})\n";

$preCount = DB::table($table)->where('email', $email)->count();
$preCountAlt = DB::table('password_resets')->where('email', $email)->count();

echo "Pre-count ($table): $preCount\n";
echo "Pre-count (password_resets): $preCountAlt\n";

try {
    Log::info('Debug send-reset attempt', ['email' => $email, 'broker' => $broker, 'table' => $table]);

    $status = Password::sendResetLink(['email' => $email]);

    echo "Broker status: $status\n";
    Log::info('Debug send-reset result', ['email' => $email, 'status' => $status]);

    $postCount = DB::table($table)->where('email', $email)->count();
    $postCountAlt = DB::table('password_resets')->where('email', $email)->count();

    echo "Post-count ($table): $postCount\n";
    echo "Post-count (password_resets): $postCountAlt\n";

    $tokenRow = DB::table($table)->where('email', $email)->latest('created_at')->first();
    echo "Token row in $table: "; var_export($tokenRow); echo PHP_EOL;

    $tokenRowAlt = DB::table('password_resets')->where('email', $email)->latest('created_at')->first();
    echo "Token row in password_resets: "; var_export($tokenRowAlt); echo PHP_EOL;

} catch (\Throwable $e) {
    echo "Exception during sendResetLink: " . $e->getMessage() . PHP_EOL;
    Log::error('Debug send-reset exception', ['email' => $email, 'exception' => $e->getMessage()]);
}

echo "Done.\n";
