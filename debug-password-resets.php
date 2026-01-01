<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = $argv[1] ?? 'reset-test@example.com';
$tokenRow = Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->latest('created_at')->first();
if ($tokenRow) {
    echo "Found token row: ".json_encode($tokenRow)."\n";
} else {
    echo "No token found for $email\n";
}
