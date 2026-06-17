<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('reports')->insert([
            [
                'title' => 'Weekly Performance Report',
                'body' => 'Beneficiaries performed well this week.',
                'employer_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Attendance Monitoring',
                'body' => 'Attendance rate improved by 15%.',
                'employer_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Work Output Summary',
                'body' => 'Most assigned tasks were completed successfully.',
                'employer_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}