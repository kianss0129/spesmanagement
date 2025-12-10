<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employer;
use App\Models\JobListing;

class EmployerSeeder extends Seeder
{
    public function run()
    {
        // Create 10 employers without job listings
        Employer::factory(10)->create();

        // Create 5 employers WITH job listings
        Employer::factory(5)->create()->each(function ($employer) {
            JobListing::factory(rand(1, 3))->create([
                'employer_id' => $employer->id,
            ]);
        });
    }
}
