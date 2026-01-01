<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Running debug-post-forgot.php\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;

$email = $argv[1] ?? 'reset-test@example.com';
if ($app->bound('session.store')) {
    $session = $app['session.store'];
    $session->start();
    $request = Request::create('/forgot-password', 'POST', [
        'email' => $email,
        '_token' => $session->token(),
    ]);
    $request->setLaravelSession($session);
    $request->headers->set('X-CSRF-TOKEN', $session->token());
    $request->cookies->set($session->getName(), $session->getId());
} else {
    // Session store not bound in this runtime (console). Fall back to direct POST without CSRF token and call controller directly to bypass CSRF middleware.
    $request = Request::create('/forgot-password', 'POST', ['email' => $email]);
}

try {
    $response = $kernel->handle($request);
    // If CSRF middleware returned a 419, call controller directly to bypass middleware and capture the broker status.
    if ($response->getStatusCode() === 419) {
        echo "Kernel returned 419; skipping controller invocation (CSRF). Will invoke Password broker directly.\n";
    }
} catch (\Throwable $e) {
    echo "Kernel handle failed: " . $e->getMessage() . "\n";
    // Call controller method directly to bypass middleware (useful in console context)
    $controller = $app->make(\App\Http\Controllers\Auth\PasswordResetLinkController::class);
    $response = $controller->store($request);
}

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: \n" . (string) $response->getContent() . "\n";

// Also invoke the Password broker directly to capture its return value and any DB queries it executes.
echo "\nDirect broker invocation...\n";
$queries = [];
\Illuminate\Support\Facades\DB::listen(function ($q) use (&$queries) {
    $queries[] = ['sql' => $q->sql, 'bindings' => $q->bindings, 'time' => $q->time];
});

$broker = \Illuminate\Support\Facades\Password::broker();
echo "Broker class: " . get_class($broker) . "\n";
$user = \App\Models\User::where('email', $email)->first();
echo "User found: " . ($user ? $user->id : 'no') . "\n";

try {
    $token = $broker->createToken($user);
    echo "createToken returned token string? length=" . strlen($token) . "\n";
} catch (\Throwable $e) {
    echo "createToken threw: " . $e->getMessage() . "\n";
}

$status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);
echo "sendResetLink status: " . var_export($status, true) . "\n";
echo "DB queries observed: " . json_encode($queries) . "\n";

// Inspect both known reset tables for rows
$rows1 = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->get();
$rows2 = \Illuminate\Support\Facades\DB::table('password_resets')->where('email', $email)->get();
echo "password_reset_tokens count: " . $rows1->count() . "\n";
echo "password_resets count: " . $rows2->count() . "\n";

if (isset($kernel)) {
    $kernel->terminate($request, $response);
}
