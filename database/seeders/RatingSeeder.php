<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployerRating;
use App\Models\Application;

class RatingSeeder extends Seeder
{
    public function run(): void
    {
        $applications = Application::all();

        foreach ($applications->take(50) as $application) {

            if (!$application->jobListing) {
                continue;
            }

            EmployerRating::create([
                'beneficiary_id' => $application->beneficiary_id,
                'employer_id' => $application->jobListing->employer_id,
                'application_id' => $application->id,

                'punctuality' => rand(3, 5),
                'work_attitude' => rand(3, 5),
                'output_quality' => rand(3, 5),
                'communication' => rand(3, 5),
                'overall' => rand(3, 5),

                'comment' => fake()->sentence(),
            ]);
        }
    }
}