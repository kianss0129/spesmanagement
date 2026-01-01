<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

echo "Running debug-invoke-route.php\n";
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;

$email = $argv[1] ?? 'reset-test@example.com';
$uri = '/_debug/send-reset?email=' . urlencode($email);

$request = Request::create($uri, 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: \n" . (string) $response->getContent() . "\n";

$kernel->terminate($request, $response);
