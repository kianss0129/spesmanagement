<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$email = 'reset-test@example.com';
$row = DB::table('users')->where('email', $email)->first();
if ($row) {
    echo "Found user: ".json_encode(['id' => $row->id, 'email' => $row->email])."\n";
} else {
    echo "No user found\n";
}
