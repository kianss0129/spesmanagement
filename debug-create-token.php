<?php
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Running debug-create-token.php\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'reset-test@example.com';
$user = App\Models\User::where('email', $email)->first();
if (! $user) {
    echo "No user found for $email\n";
    exit(1);
}

$brokerName = config('auth.defaults.passwords');
echo "Broker name: $brokerName\n";

echo 'App has auth binding: ' . (isset($app['auth']) ? 'yes' : 'no') . PHP_EOL;
echo 'App has auth.password binding: ' . (isset($app['auth.password']) ? 'yes' : 'no') . PHP_EOL;
try {
    $authPassword = $app['auth.password'];
    echo 'auth.password class: ' . (is_object($authPassword) ? get_class($authPassword) : var_export($authPassword, true)) . PHP_EOL;
} catch (\Throwable $e) {
    echo 'Exception fetching auth.password: ' . $e->getMessage() . PHP_EOL;
}

try {
    // Try resolving via container directly instead of facade (works around facade issues)
    $broker = $app['auth.password']->broker($brokerName);
    echo "Broker resolved via container\n";
} catch (\Throwable $e) {
    echo "Exception resolving broker: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo "About to call createToken()\n";
try {
    echo "Calling createToken() now...\n";
    $token = $broker->createToken($user);
    echo "Token created: ".$token.PHP_EOL;

    $table = config("auth.passwords.$brokerName.table");
    echo "Checking table: $table\n";
    $row = DB::table($table)->where('email', $email)->latest('created_at')->first();
    echo "Row: "; var_export($row); echo PHP_EOL;
} catch (\Throwable $e) {
    echo "Exception: ".$e->getMessage().PHP_EOL;
}