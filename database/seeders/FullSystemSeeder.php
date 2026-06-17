<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employer;
use App\Models\Beneficiary;
use App\Models\JobListing;
use App\Models\Application;
use App\Models\Interview;
use Illuminate\Support\Facades\Hash;

class FullSystemSeeder extends Seeder
{
    public function run(): void
    {
        $employers = [];
        $beneficiaries = [];
        $jobs = [];

        /*
        |--------------------------------------------------------------------------
        | EMPLOYERS
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 10; $i++) {

            $user = User::create([
                'name' => "Employer {$i}",
                'email' => "employer{$i}@spes.test",
                'password' => Hash::make('password'),
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('employer');
            }

            $employer = Employer::create([
                'user_id' => $user->id,
                'company_name' => fake()->company(),
                'contact_person' => fake()->name(),
                'email' => $user->email,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'approved' => true,
                'approval_status' => 'approved',
                'status' => 'active',
                'onboarding_completed_at' => now(),
            ]);

            $employers[] = $employer;
        }

        /*
        |--------------------------------------------------------------------------
        | BENEFICIARIES
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 80; $i++) {

            $user = User::create([
                'name' => fake()->name(),
                'email' => "beneficiary{$i}@spes.test",
                'password' => Hash::make('password'),
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole('beneficiary');
            }

            $status = fake()->randomElement([
                'approved',
                'approved',
                'approved',
                'pending',
                'rejected'
            ]);

            $beneficiary = Beneficiary::create([
                'user_id' => $user->id,
                'student_id' => '2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => $user->email,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'gender' => fake()->randomElement(['male', 'female']),
                'program' => fake()->randomElement([
                    'BSIT',
                    'BSBA',
                    'BSHM',
                    'BSEd',
                    'BSCS',
                ]),
                'year_level' => rand(1, 4),
                'status' => $status,
                'approved' => $status === 'approved',
                'approval_status' => $status,
                'approved_at' => now(),
                'employment_status' => fake()->randomElement([
                    'unassigned',
                    'assigned',
                    'completed'
                ]),
                'onboarding_completed_at' => now(),
            ]);

            $beneficiaries[] = $beneficiary;
        }

        /*
        |--------------------------------------------------------------------------
        | JOB LISTINGS
        |--------------------------------------------------------------------------
        */

        foreach ($employers as $employer) {

            for ($j = 1; $j <= 4; $j++) {

                $job = JobListing::create([
                    'employer_id' => $employer->id,
                    'title' => fake()->randomElement([
                        'Office Assistant',
                        'Data Encoder',
                        'IT Support',
                        'Clerk',
                        'Administrative Assistant',
                    ]),
                    'description' => fake()->paragraph(),
                    'location' => 'Quezon City',
                    'type' => fake()->randomElement([
                        'Part-time',
                        'Full-time'
                    ]),
                    'slots' => rand(1, 5),
                    'salary' => rand(500, 900),
                    'closing_date' => now()->addDays(rand(5, 30)),
                ]);

                $jobs[] = $job;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | APPLICATIONS
        |--------------------------------------------------------------------------
        */

        foreach ($beneficiaries as $beneficiary) {

            $job = collect($jobs)->random();

            $application = Application::create([
                'beneficiary_id' => $beneficiary->id,
                'job_listing_id' => $job->id,
                'status' => fake()->randomElement([
                    'pending',
                    'approved',
                    'interview',
                    'hired',
                    'rejected'
                ]),
            ]);

            /*
            |--------------------------------------------------------------------------
            | INTERVIEWS
            |--------------------------------------------------------------------------
            */

            Interview::create([
                'job_listing_id' => $job->id,
                'employer_id' => $job->employer_id,
                'beneficiary_id' => $beneficiary->id,
                'application_id' => $application->id,
                'scheduled_at' => now()->addDays(rand(1, 15)),
                'meet_link' => 'https://meet.google.com/sample',
                'status' => fake()->randomElement([
                    'scheduled',
                    'completed'
                ]),
            ]);
        }
    }
}