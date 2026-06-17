<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Application;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $applications = Application::all();

        foreach ($applications as $application) {

            for ($i = 1; $i <= 15; $i++) {

                Attendance::create([
                    'beneficiary_id' => $application->beneficiary_id,
                    'application_id' => $application->id,
                    'date' => now()->subDays(rand(1, 30)),
                    'time_in' => '08:00:00',
                    'time_out' => '05:00:00',
                    'status' => fake()->randomElement([
                        'present',
                        'late',
                        'absent'
                    ]),
                    'remarks' => fake()->sentence(),
                ]);
            }
        }
    }
}