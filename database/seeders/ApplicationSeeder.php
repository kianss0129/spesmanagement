<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use App\Models\EmployerRating;
use App\Models\Beneficiary;
use App\Models\JobListing;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $beneficiaries = Beneficiary::all();
        $jobs = JobListing::all();

        if ($beneficiaries->isEmpty() || $jobs->isEmpty()) {
            $this->command->warn(
                'Skipping ApplicationSeeder: beneficiaries or job listings table is empty.'
            );
            return;
        }

        foreach ($beneficiaries as $b) {
            $job = $jobs->random();

            $app = Application::create([
                'beneficiary_id' => $b->id,
                'job_listing_id' => $job->id,
                'status' => collect(['applied','interviewed','hired','rejected'])->random(),
            ]);

            if ($app->status === 'hired') {
                EmployerRating::create([
                    'employer_id' => $job->employer_id,
                    'beneficiary_id' => $b->id,
                    'application_id' => $app->id,
                    'punctuality' => rand(3,5),
                    'work_attitude' => rand(3,5),
                    'output_quality' => rand(3,5),
                    'communication' => rand(3,5),
                    'overall' => rand(3,5),
                    'comment' => 'Sample rating comment',
                ]);
            }
        }
    }
}
