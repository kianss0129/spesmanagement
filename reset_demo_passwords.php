<?php
/**
 * EMERGENCY PASSWORD RESET FOR DEMO
 * Run: php reset_demo_passwords.php
 * 
 * Sets all demo accounts to password: password123
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$password = Hash::make('password123');

$users = User::all();
foreach ($users as $user) {
    $user->update(['password' => $password]);
    echo "Reset: {$user->email} => password123\n";
}

echo "\n✅ All passwords reset to: password123\n";
echo "🔑 You can now login with any account using this password.\n";
