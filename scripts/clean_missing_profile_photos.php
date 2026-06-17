<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\User;

$disk = config('filesystems.default');
echo "Using disk: {$disk}\n";
$users = User::whereNotNull('profile_photo_path')->get();
echo "Found " . $users->count() . " users with profile_photo_path set\n";
$fixed = 0;
foreach ($users as $user) {
    $path = $user->profile_photo_path;
    if (! $path) continue;
    if (! Storage::disk($disk)->exists($path)) {
        echo "Nullifying user {$user->id} ({$user->email}) path={$path}\n";
        $user->profile_photo_path = null;
        $user->save();
        $fixed++;
    }
}

echo "Done. Nullified {$fixed} users.\n";
