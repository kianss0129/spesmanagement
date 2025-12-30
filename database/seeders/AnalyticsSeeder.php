<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Analytics;

class AnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        Analytics::create([
            'type' => 'applicant_trend',
            'data' => [
                'labels' => ['School A', 'School B', 'School C'],
                'data' => [10, 15, 8],
            ],
        ]);

        Analytics::create([
            'type' => 'top_employers',
            'data' => [
                'labels' => ['Company X', 'Company Y', 'Company Z'],
                'data' => [4.5, 4.2, 3.8],
            ],
        ]);
    }
}
