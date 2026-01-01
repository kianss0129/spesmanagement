<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Running debug-config.php\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "auth.defaults.passwords: ".(config('auth.defaults.passwords') ?? 'NULL')."\n";
