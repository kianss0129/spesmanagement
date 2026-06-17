<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class CleanMissingProfilePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:clean-missing-profile-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nullify profile_photo_path for users whose files are missing from storage';

    public function handle()
    {
        $disk = config('filesystems.default');
        $this->info("Checking users for missing profile photos on disk: {$disk}");

        $users = User::whereNotNull('profile_photo_path')->get();
        $total = $users->count();
        $this->info("Found {$total} users with a profile_photo_path set");

        $fixed = 0;

        foreach ($users as $user) {
            $path = $user->profile_photo_path;

            if (! $path) {
                continue;
            }

            if (! Storage::disk($disk)->exists($path)) {
                $this->line("- Missing for user {$user->id} ({$user->email}): {$path} -> nullifying");
                $user->profile_photo_path = null;
                $user->save();
                $fixed++;
            }
        }

        $this->info("Completed. Nullified profile_photo_path for {$fixed} users.");

        return 0;
    }
}
