<?php

namespace Database\Seeders;

use App\Models\Employer;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployerJobTestSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'Employer', 'guard_name' => 'web']);

        $employers = [
            [
                'company_name' => 'BrightCompany',
                'contact_person' => 'Maria Santos',
                'email' => 'brightcompany@example.test',
                'phone' => '0917-555-0101',
                'address' => 'Rizal Street, Santa Cruz, Laguna',
                'jobs' => [
                    [
                        'title' => 'Office Assistant',
                        'description' => 'Assist with filing, encoding, and front desk support.',
                        'location' => 'Santa Cruz, Laguna',
                        'type' => 'Part-time',
                        'slots' => 4,
                        'closing_date' => now()->addDays(30)->toDateString(),
                        'employer_choice' => 'approved',
                        'created_at' => now()->subDays(14),
                    ],
                    [
                        'title' => 'Inventory Clerk',
                        'description' => 'Help monitor supplies and update inventory records.',
                        'location' => 'Pila, Laguna',
                        'type' => 'Full-time',
                        'slots' => 3,
                        'closing_date' => now()->addDays(35)->toDateString(),
                        'employer_choice' => 'pending',
                        'created_at' => now()->subDays(10),
                    ],
                    [
                        'title' => 'Customer Service Aide',
                        'description' => 'Support customer inquiries and basic office transactions.',
                        'location' => 'Calamba, Laguna',
                        'type' => 'Part-time',
                        'slots' => 2,
                        'closing_date' => now()->addDays(45)->toDateString(),
                        'employer_choice' => 'rejected',
                        'created_at' => now()->subDays(6),
                    ],
                ],
            ],
            [
                'company_name' => 'LagunaTech Solutions',
                'contact_person' => 'Jose Ramirez',
                'email' => 'lagunatech@example.test',
                'phone' => '0917-555-0102',
                'address' => 'National Highway, Los Banos, Laguna',
                'jobs' => [
                    [
                        'title' => 'Data Encoder',
                        'description' => 'Encode client records and organize digital files.',
                        'location' => 'Los Banos, Laguna',
                        'type' => 'Part-time',
                        'slots' => 5,
                        'closing_date' => now()->addDays(25)->toDateString(),
                        'employer_choice' => 'approved',
                        'created_at' => now()->subDays(12),
                    ],
                    [
                        'title' => 'IT Support Trainee',
                        'description' => 'Assist with basic troubleshooting and workstation setup.',
                        'location' => 'Calamba, Laguna',
                        'type' => 'Full-time',
                        'slots' => 2,
                        'closing_date' => now()->addDays(40)->toDateString(),
                        'employer_choice' => 'pending',
                        'created_at' => now()->subDays(8),
                    ],
                ],
            ],
            [
                'company_name' => 'GreenFields Market',
                'contact_person' => 'Ana Dela Cruz',
                'email' => 'greenfields@example.test',
                'phone' => '0917-555-0103',
                'address' => 'Public Market Road, Victoria, Laguna',
                'jobs' => [
                    [
                        'title' => 'Store Assistant',
                        'description' => 'Assist with shelf arrangement and customer support.',
                        'location' => 'Victoria, Laguna',
                        'type' => 'Part-time',
                        'slots' => 6,
                        'closing_date' => now()->addDays(20)->toDateString(),
                        'employer_choice' => 'approved',
                        'created_at' => now()->subDays(9),
                    ],
                    [
                        'title' => 'Packaging Helper',
                        'description' => 'Help prepare, label, and organize packed goods.',
                        'location' => 'Pagsanjan, Laguna',
                        'type' => 'Full-time',
                        'slots' => 4,
                        'closing_date' => now()->addDays(28)->toDateString(),
                        'employer_choice' => 'rejected',
                        'created_at' => now()->subDays(5),
                    ],
                ],
            ],
        ];

        foreach ($employers as $employerData) {
            $user = User::updateOrCreate(
                ['email' => $employerData['email']],
                [
                    'name' => $employerData['contact_person'],
                    'password' => Hash::make('password'),
                ]
            );

            $user->forceFill([
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            if (! $user->hasRole('Employer')) {
                $user->assignRole('Employer');
            }

            $employer = Employer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'company_name' => $employerData['company_name'],
                    'contact_person' => $employerData['contact_person'],
                    'email' => $employerData['email'],
                    'phone' => $employerData['phone'],
                    'address' => $employerData['address'],
                    'approved' => true,
                    'approval_status' => 'approved',
                    'status' => 'active',
                    'approved_at' => now(),
                    'onboarding_completed_at' => now(),
                ]
            );

            foreach ($employerData['jobs'] as $jobData) {
                $job = JobListing::updateOrCreate(
                    [
                        'employer_id' => $employer->id,
                        'title' => $jobData['title'],
                    ],
                    [
                        'description' => $jobData['description'],
                        'location' => $jobData['location'],
                        'type' => $jobData['type'],
                        'slots' => $jobData['slots'],
                        'closing_date' => $jobData['closing_date'],
                    ]
                );

                $job->forceFill([
                    'employer_choice' => $jobData['employer_choice'],
                    'created_at' => $jobData['created_at'],
                ])->save();
            }
        }
    }
}
