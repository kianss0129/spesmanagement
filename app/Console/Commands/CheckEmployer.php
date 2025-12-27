<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CheckEmployer extends Command
{
    protected $signature = 'app:check-employer';
    protected $description = 'Check employer-related tables and stored files';

    public function handle()
    {
        $tables = ['job_listings','applications','reports','work_outputs','attendances','interviews'];
        $this->info('Checking tables: ' . implode(',', $tables));
        $out = [];
        foreach ($tables as $t) {
            try {
                $count = DB::table($t)->count();
                $out[$t] = $count;
            } catch (\Exception $e) {
                $out[$t] = 'MISSING';
            }
        }

        $this->line(json_encode($out, JSON_PRETTY_PRINT));

        $this->info('Work outputs in storage/app/public/work_outputs:');
        $files = Storage::disk('public')->files('work_outputs');
        foreach ($files as $f) {
            $this->line('- ' . $f);
        }

        return 0;
    }
}
