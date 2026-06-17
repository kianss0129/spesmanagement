<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\Models\Role;

class SpesDemoSeeder extends Seeder
{
    private array $columns = [];

    private array $skippedFields = [];

    private array $counts = [];

    private array $statuses = [
        'applied',
        'needs_correction',
        'for_exam',
        'exam_passed',
        'for_interview',
        'interview_passed',
        'needs_review',
        'qualified',
        'approved',
        'assigned',
        'for_contract',
        'contract_signed',
        'deployed',
        'ongoing',
        'completion_review',
        'completed',
        'rejected',
    ];

    private array $demoStatusDistribution = [
        'applied',
        'needs_correction',
        'for_exam',
        'exam_passed',
        'for_interview',
        'interview_passed',
        'needs_review',
        'qualified',
        'approved',
        'assigned',
        'for_contract',
        'contract_signed',
        'deployed',
        'ongoing',
        'completion_review',
        'completed',
        'rejected',
        'applied',
        'needs_correction',
        'for_exam',
        'exam_passed',
        'for_interview',
        'interview_passed',
        'needs_review',
        'qualified',
        'deployed',
        'assigned',
        'for_contract',
        'contract_signed',
        'ongoing',
    ];

    private array $barangays = [
        'San Agustin',
        'Dolores',
        'Sindalan',
        'Maimpis',
        'Sto. Rosario',
        'San Jose',
        'Telabastagan',
        'Del Carmen',
        'Calulut',
        'Santo Nino',
    ];

    private array $firstNames = [
        'Juan Miguel',
        'Maria Angelica',
        'Jose Carlo',
        'Andrea Mae',
        'Rafael',
        'Bianca',
        'Mark Anthony',
        'Patricia',
        'Christian',
        'Jasmine',
        'Gabriel',
        'Camille',
        'Joshua',
        'Alyssa',
        'Nathaniel',
    ];

    private array $lastNames = [
        'Dizon',
        'Manaloto',
        'Pineda',
        'Mallari',
        'Santos',
        'Garcia',
        'Yabut',
        'Lacson',
        'Guevarra',
        'Reyes',
        'Tolentino',
        'Aquino',
        'David',
        'Canlas',
        'Ocampo',
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            throw new RuntimeException('SpesDemoSeeder is development-only and was blocked outside local/development/testing.');
        }

        $this->cacheColumns();

        DB::transaction(function () {
            $this->seedRoles();

            $admin = $this->demoUser('Admin Demo', 'admin@spes.test', 'Admin');
            $pesoUsers = [
                $this->demoUser('PESO Interviewer 1', 'peso1@spes.test', 'PESO'),
                $this->demoUser('PESO Interviewer 2', 'peso2@spes.test', 'PESO'),
                $this->demoUser('PESO Interviewer 3', 'peso3@spes.test', 'PESO'),
            ];

            $schools = $this->seedSchools();
            $skillCategories = $this->seedSkillCategories();
            $skills = $this->seedSkills($skillCategories);
            $employers = $this->seedEmployers($admin);
            $jobs = $this->seedJobs($employers, $skills);
            $beneficiaries = $this->seedBeneficiaries($schools, $skills);
            $applications = $this->seedApplications($admin, $beneficiaries, $jobs, $employers);

            $this->seedExams($admin, $applications);
            $this->seedInterviews($admin, $pesoUsers, $applications);
            $this->seedContracts($admin, $applications);
            $this->seedAttendance($applications);
            $this->seedWorkOutputs($applications);
            $this->seedEmployerRatings($applications);
            $this->seedAnnouncements($admin);
            $this->seedReports($employers);
        });

        $this->printSummary();
    }

    private function cacheColumns(): void
    {
        foreach ([
            'users',
            'employers',
            'beneficiaries',
            'applications',
            'job_listings',
            'exams',
            'interviews',
            'contracts',
            'attendances',
            'work_outputs',
            'employer_ratings',
            'announcements',
            'reports',
            'schools',
            'skill_categories',
            'skills',
            'beneficiary_skill',
            'job_listing_skills',
        ] as $table) {
            $this->columns[$table] = Schema::hasTable($table)
                ? array_flip(Schema::getColumnListing($table))
                : [];
        }
    }

    private function seedRoles(): void
    {
        foreach (['Admin', 'PESO', 'Employer', 'Beneficiary'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
            $this->bump('roles');
        }
    }

    private function demoUser(string $name, string $email, string $role, ?string $beneficiaryType = null): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill($this->filter('users', [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => strtolower($role),
            'beneficiary_type' => $beneficiaryType,
            'onboarding_completed' => true,
            'is_temporary_password' => false,
            'created_at' => $user->exists ? $user->created_at : now(),
            'updated_at' => now(),
        ]));
        $user->save();
        $user->syncRoles([$role]);

        $this->bump('users');

        return $user;
    }

    private function seedSchools(): array
    {
        $schools = [
            ['name' => 'Pampanga State Agricultural University', 'address' => 'Magalang, Pampanga', 'contact_number' => '045-866-0800'],
            ['name' => 'Don Honorio Ventura State University', 'address' => 'Bacolor, Pampanga', 'contact_number' => '045-458-0021'],
            ['name' => 'City College of San Fernando', 'address' => 'San Fernando, Pampanga', 'contact_number' => '045-961-8957'],
            ['name' => 'Holy Angel University', 'address' => 'Angeles City, Pampanga', 'contact_number' => '045-888-8691'],
            ['name' => 'Pampanga High School', 'address' => 'San Fernando, Pampanga', 'contact_number' => '045-961-3620'],
            ['name' => 'Mabalacat City College', 'address' => 'Mabalacat City, Pampanga', 'contact_number' => '045-893-0441'],
        ];

        return array_map(fn (array $school) => $this->upsert('schools', ['name' => $school['name']], [
            ...$school,
            'created_at' => now()->subDays(45),
            'updated_at' => now(),
        ]), $schools);
    }

    private function seedSkillCategories(): array
    {
        $names = ['Office Support', 'Digital Skills', 'Community Service', 'Communication', 'Records Management'];
        $categories = [];

        foreach ($names as $index => $name) {
            if ($this->hasColumn('skill_categories', 'name')) {
                $categories[] = $this->upsert('skill_categories', ['name' => $name], [
                    'name' => $name,
                    'created_at' => now()->subDays(45),
                    'updated_at' => now(),
                ]);
                continue;
            }

            $existing = DB::table('skill_categories')->skip($index)->first();
            if ($existing) {
                $categories[] = $existing;
                continue;
            }

            $id = DB::table('skill_categories')->insertGetId($this->filter('skill_categories', [
                'created_at' => now()->subDays(45),
                'updated_at' => now(),
            ]));
            $categories[] = DB::table('skill_categories')->find($id);
            $this->bump('skill_categories');
        }

        return $categories;
    }

    private function seedSkills(array $categories): array
    {
        $skills = [
            ['Data Encoding', 'Office Support'],
            ['Filing and Records Sorting', 'Records Management'],
            ['Microsoft Word', 'Digital Skills'],
            ['Microsoft Excel', 'Digital Skills'],
            ['Google Workspace', 'Digital Skills'],
            ['Public Assistance Desk', 'Communication'],
            ['Document Routing', 'Office Support'],
            ['Basic Graphic Layout', 'Digital Skills'],
            ['Community Survey Support', 'Community Service'],
            ['Inventory Assistance', 'Office Support'],
            ['Library Cataloging', 'Records Management'],
            ['Event Registration Support', 'Communication'],
            ['Phone Inquiry Handling', 'Communication'],
            ['Report Preparation', 'Records Management'],
            ['Social Media Posting', 'Digital Skills'],
            ['Cleaning and Organizing', 'Community Service'],
            ['Visitor Assistance', 'Communication'],
            ['Basic Research', 'Records Management'],
            ['Printing and Scanning', 'Office Support'],
            ['Barangay Outreach Support', 'Community Service'],
        ];

        return array_map(function (array $skill, int $index) use ($categories) {
            $category = $categories[$index % max(count($categories), 1)] ?? null;

            return $this->upsert('skills', ['name' => $skill[0]], [
                'name' => $skill[0],
                'description' => 'Demo skill for SPES matching: ' . $skill[0],
                'category' => $skill[1],
                'skill_category_id' => $category?->id,
                'created_at' => now()->subDays(45),
                'updated_at' => now(),
            ]);
        }, $skills, array_keys($skills));
    }

    private function seedEmployers(User $admin): array
    {
        $profiles = [
            [
                'email' => 'employer1@spes.test',
                'name' => 'Pampanga Provincial Library',
                'contact' => 'Lourdes Manaloto',
                'phone' => '0917-555-0101',
                'address' => 'Capitol Compound, City of San Fernando, Pampanga',
            ],
            [
                'email' => 'employer2@spes.test',
                'name' => 'San Fernando City Tourism Office',
                'contact' => 'Ramon Dizon',
                'phone' => '0917-555-0102',
                'address' => 'City Hall, San Fernando, Pampanga',
            ],
            [
                'email' => 'employer3@spes.test',
                'name' => 'Mabalacat Community Office',
                'contact' => 'Cecilia Pineda',
                'phone' => '0917-555-0103',
                'address' => 'Dau, Mabalacat City, Pampanga',
            ],
        ];

        $employers = [];

        foreach ($profiles as $profile) {
            $user = $this->demoUser($profile['name'], $profile['email'], 'Employer');
            $employers[] = Employer::updateOrCreate(
                ['user_id' => $user->id],
                $this->filter('employers', [
                    'company_name' => $profile['name'],
                    'contact_person' => $profile['contact'],
                    'email' => $profile['email'],
                    'phone' => $profile['phone'],
                    'address' => $profile['address'],
                    'documents' => [
                        'business_permit' => 'demo/employers/business-permit.pdf',
                        'dole_registration' => 'demo/employers/dole-registration.pdf',
                    ],
                    'details' => [
                        'industry' => 'Government / Community Service',
                        'spes_slots' => 10,
                    ],
                    'onboarding_completed_at' => now()->subDays(35),
                    'approved' => true,
                    'approval_status' => 'approved',
                    'approved_at' => now()->subDays(34),
                    'approved_by' => $admin->id,
                    'status' => 'active',
                    'created_at' => now()->subDays(40),
                    'updated_at' => now(),
                ])
            );
            $this->bump('employers');
        }

        return $employers;
    }

    private function seedJobs(array $employers, array $skills): array
    {
        $jobs = [];
        $titles = [
            ['Library Assistant', 'Assist with cataloging, shelf organization, and reader assistance.'],
            ['Records Encoding Assistant', 'Encode and validate office documents for daily transactions.'],
            ['Public Assistance Aide', 'Help route clients and answer basic office inquiries.'],
            ['Tourism Desk Assistant', 'Assist visitors and prepare tourism information materials.'],
            ['Events Registration Aide', 'Support city event registration and attendance encoding.'],
            ['Information Materials Assistant', 'Assist with printing, sorting, and posting advisories.'],
            ['Community Records Assistant', 'Encode barangay and community service records.'],
            ['Inventory Support Aide', 'Assist with supply inventory and filing.'],
            ['Office Support Trainee', 'Perform supervised clerical tasks for SPES deployment.'],
        ];

        foreach ($titles as $index => [$title, $description]) {
            $employer = $employers[$index % count($employers)];
            $job = $this->upsert('job_listings', [
                'employer_id' => $employer->id,
                'title' => 'SPES Demo - ' . $title,
            ], [
                'employer_id' => $employer->id,
                'title' => 'SPES Demo - ' . $title,
                'description' => $description,
                'location' => $employer->address,
                'salary' => 520.00,
                'type' => 'SPES',
                'slots' => 8,
                'closing_date' => now()->addMonth()->toDateString(),
                'created_at' => now()->subDays(25),
                'updated_at' => now(),
            ]);
            $jobs[] = $job;

            foreach (array_slice($skills, ($index * 2) % count($skills), 3) as $skill) {
                $this->upsert('job_listing_skills', [
                    'job_listing_id' => $job->id,
                    'skill_id' => $skill->id,
                ], [
                    'job_listing_id' => $job->id,
                    'skill_id' => $skill->id,
                    'created_at' => now()->subDays(20),
                    'updated_at' => now(),
                ]);
            }
        }

        return $jobs;
    }

    private function seedBeneficiaries(array $schools, array $skills): array
    {
        $beneficiaries = [];
        $groups = [
            ['prefix' => 'student', 'type' => 'student', 'category' => 'Student'],
            ['prefix' => 'osy', 'type' => 'osy', 'category' => 'OSY'],
            ['prefix' => 'ddw', 'type' => 'dependent', 'category' => 'DDW'],
        ];

        foreach ($groups as $groupIndex => $group) {
            for ($i = 1; $i <= 10; $i++) {
                $firstName = $this->firstNames[($i - 1) % count($this->firstNames)];
                $lastName = $this->lastNames[($i + $groupIndex) % count($this->lastNames)];
                $email = "{$group['prefix']}{$i}@spes.test";
                $school = $schools[($i + $groupIndex) % count($schools)];
                $user = $this->demoUser("{$firstName} {$lastName}", $email, 'Beneficiary', $group['type']);

                $beneficiary = Beneficiary::updateOrCreate(
                    ['user_id' => $user->id],
                    $this->filter('beneficiaries', [
                        'first_name' => $firstName,
                        'middle_name' => 'Santos',
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => '0917-600-' . str_pad((string) (($groupIndex * 10) + $i), 4, '0', STR_PAD_LEFT),
                        'contact_number' => '0917-600-' . str_pad((string) (($groupIndex * 10) + $i), 4, '0', STR_PAD_LEFT),
                        'birth_date' => now()->subYears(18 + ($i % 5))->subDays($i)->toDateString(),
                        'birthdate' => now()->subYears(18 + ($i % 5))->subDays($i)->toDateString(),
                        'age' => 18 + ($i % 5),
                        'sex' => $i % 2 ? 'Male' : 'Female',
                        'gender' => $i % 2 ? 'Male' : 'Female',
                        'civil_status' => 'Single',
                        'place_of_birth' => 'City of San Fernando, Pampanga',
                        'citizenship' => 'Filipino',
                        'category' => $group['category'],
                        'school_id' => $school?->id,
                        'present_address' => $this->barangays[$i - 1] . ', City of San Fernando, Pampanga',
                        'address' => $this->barangays[$i - 1] . ', City of San Fernando, Pampanga',
                        'barangay' => $this->barangays[$i - 1],
                        'city' => 'City of San Fernando',
                        'province' => 'Pampanga',
                        'father_name' => 'Pedro ' . $lastName,
                        'mother_name' => 'Maria ' . $lastName,
                        'parent_guardian_name' => 'Maria ' . $lastName,
                        'relationship' => 'Mother',
                        'family_income' => 8500 + ($i * 350),
                        'school_name' => $group['type'] === 'student' ? $school?->name : null,
                        'school_address' => $group['type'] === 'student' ? $school?->address : null,
                        'education_level' => $group['type'] === 'student' ? 'College' : 'Senior High School Graduate',
                        'school_year' => '2026-2027',
                        'course' => $group['type'] === 'student' ? 'BS Information Technology' : null,
                        'last_school_attended' => $group['type'] !== 'student' ? $school?->name : null,
                        'highest_attainment' => $group['type'] !== 'student' ? 'Senior High School' : null,
                        'displacement_reason' => $group['type'] === 'dependent' ? 'Parent affected by temporary work displacement.' : null,
                        'former_employer' => $group['type'] === 'dependent' ? 'Local garments shop' : null,
                        'displacement_date' => $group['type'] === 'dependent' ? now()->subMonths(3)->toDateString() : null,
                        'previous_spes' => false,
                        'spes_count' => 0,
                        'documents' => $this->beneficiaryDocuments($group['type']),
                        'submitted_at' => now()->subDays(20),
                        'onboarding_completed_at' => now()->subDays(20),
                        'onboarding_step' => 4,
                        'completion_percentage' => 100,
                        'completed_steps' => ['profile', 'documents', 'review'],
                        'approved' => false,
                        'approval_status' => 'pending',
                        'status' => 'pending',
                        'employment_status' => 'unassigned',
                        'created_at' => now()->subDays(25),
                        'updated_at' => now(),
                    ])
                );
                $this->bump('beneficiaries');

                foreach (array_slice($skills, (($groupIndex * 10 + $i) * 2) % count($skills), 4) as $skill) {
                    $this->upsert('beneficiary_skill', [
                        'beneficiary_id' => $beneficiary->id,
                        'skill_id' => $skill->id,
                    ], [
                        'beneficiary_id' => $beneficiary->id,
                        'skill_id' => $skill->id,
                        'created_at' => now()->subDays(18),
                        'updated_at' => now(),
                    ]);
                }

                $beneficiaries[] = $beneficiary;
            }
        }

        return $beneficiaries;
    }

    private function seedApplications(User $admin, array $beneficiaries, array $jobs, array $employers): array
    {
        $applications = [];

        foreach ($beneficiaries as $index => $beneficiary) {
            $status = $this->demoStatusDistribution[$index] ?? $this->statuses[$index % count($this->statuses)];
            $job = $this->requiresAssignment($status) ? $jobs[$index % count($jobs)] : null;
            $employer = $job ? $this->findEmployerForJob($employers, (int) $job->employer_id) : null;
            $isApproved = in_array($status, ['approved', 'assigned', 'for_contract', 'contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed'], true);

            $beneficiary->update($this->filter('beneficiaries', [
                'approved' => $isApproved,
                'approval_status' => $status === 'needs_correction' ? 'needs_correction' : ($status === 'rejected' ? 'rejected' : ($isApproved ? 'approved' : 'pending')),
                'approved_at' => $isApproved ? now()->subDays(12) : null,
                'approved_by' => $isApproved ? $admin->id : null,
                'status' => $status === 'needs_correction' ? 'needs_correction' : ($status === 'rejected' ? 'rejected' : ($isApproved ? 'approved' : 'pending')),
                'rejection_reason' => $status === 'needs_correction' ? 'Please re-upload a clearer copy of your requirements.' : ($status === 'rejected' ? 'Demo rejected application for workflow testing.' : null),
                'resubmit_at' => $status === 'needs_correction' ? now()->subDays(2) : null,
                'employment_status' => $this->employmentStatus($status),
                'job_id' => $job?->id,
                'employer_id' => $employer?->id,
                'completed_at' => $status === 'completed' ? now()->subDay() : null,
                'updated_at' => now(),
            ]));

            $application = Application::updateOrCreate(
                ['beneficiary_id' => $beneficiary->id],
                $this->filter('applications', [
                    'beneficiary_id' => $beneficiary->id,
                    'job_listing_id' => $job?->id,
                    'status' => $status,
                    'certificate_path' => $status === 'completed'
                        ? 'certificates/demo-completion-certificate.pdf'
                        : ($status === 'completion_review' ? 'certificates/demo-pending-certificate.pdf' : null),
                    'employer_acknowledged_at' => in_array($status, ['deployed', 'ongoing', 'completion_review', 'completed'], true) ? now()->subDays(6) : null,
                    'employer_acknowledged_by' => in_array($status, ['deployed', 'ongoing', 'completion_review', 'completed'], true) ? $employer?->user_id : null,
                    'created_at' => now()->subDays(18),
                    'updated_at' => now(),
                ])
            );
            $this->bump('applications');

            if ($job && ! $job->assigned_beneficiary_id) {
                DB::table('job_listings')
                    ->where('id', $job->id)
                    ->update($this->filter('job_listings', ['assigned_beneficiary_id' => $beneficiary->user_id]));
            }

            $application->beneficiary = $beneficiary;
            $application->job = $job;
            $application->employer = $employer;
            $applications[] = $application;
        }

        return $applications;
    }

    private function seedExams(User $admin, array $applications): void
    {
        $eligible = array_values(array_filter($applications, fn ($application) => in_array($application->status, [
            'for_exam',
            'exam_passed',
            'for_interview',
            'interview_passed',
            'needs_review',
            'qualified',
            'approved',
            'assigned',
            'for_contract',
            'contract_signed',
            'deployed',
            'ongoing',
            'completion_review',
            'completed',
            'rejected',
        ], true)));

        foreach (array_slice($eligible, 0, 20) as $index => $application) {
            $pending = $application->status === 'for_exam';
            $failed = $application->status === 'rejected' && $index % 2 === 0;
            $scheduledAt = now()->addDays($pending ? 2 : -12)->setTime(8 + ($index % 4), 30);

            $this->upsert('exams', ['application_id' => $application->id], [
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
                'exam_date' => $scheduledAt,
                'location' => 'CPESO Training Room, Capitol Compound, San Fernando, Pampanga',
                'notes' => 'Bring valid ID and black pen.',
                'status' => $pending ? 'scheduled' : 'completed',
                'result' => $pending ? 'pending' : ($failed ? 'failed' : 'passed'),
                'remarks' => $pending ? null : 'Demo exam result recorded by CPESO.',
                'schedule_group_id' => 'demo-exam-batch-2026',
                'batch_title' => 'SPES Demo Exam Batch',
                'scheduled_by' => $admin->id,
                'end_at' => (clone $scheduledAt)->addHours(2),
                'instructions' => 'Face-to-face exam at CPESO office.',
                'notify_beneficiaries' => true,
                'created_at' => now()->subDays(16),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedInterviews(User $admin, array $pesoUsers, array $applications): void
    {
        $eligible = array_values(array_filter($applications, fn ($application) => in_array($application->status, [
            'for_interview',
            'interview_passed',
            'needs_review',
            'qualified',
            'approved',
            'assigned',
            'for_contract',
            'contract_signed',
            'deployed',
            'ongoing',
            'completion_review',
            'completed',
            'rejected',
        ], true)));

        foreach (array_slice($eligible, 0, 20) as $index => $application) {
            $pending = $application->status === 'for_interview';
            $result = match ($application->status) {
                'needs_review' => 'needs_review',
                'rejected' => 'failed',
                default => $pending ? 'pending' : 'passed',
            };
            $scheduledAt = now()->addDays($pending ? 3 : -9)->setTime(9 + ($index % 5), 0);
            $meetLink = 'https://meet.google.com/spes-demo-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $isRescheduledExample = $index === 1;

            $this->upsert('interviews', ['application_id' => $application->id], [
                'job_listing_id' => $application->job_listing_id,
                'employer_id' => $application->employer?->id,
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
                'scheduled_at' => $scheduledAt,
                'google_meet_link' => $meetLink,
                'meet_link' => $meetLink,
                'calendar_event_id' => null,
                'status' => $pending ? 'scheduled' : 'completed',
                'result' => $result,
                'remarks' => $pending ? null : 'Demo interview evaluation remarks.',
                'evaluated_at' => $pending ? null : now()->subDays(8),
                'schedule_group_id' => 'demo-interview-batch-2026',
                'batch_title' => 'SPES Demo Interview Batch',
                'scheduled_by' => $admin->id,
                'interviewer_id' => $pesoUsers[$index % count($pesoUsers)]->id,
                'end_at' => (clone $scheduledAt)->addHour(),
                'original_schedule_at' => $isRescheduledExample ? (clone $scheduledAt)->subDay() : null,
                'rescheduled_at' => $isRescheduledExample ? now()->subDays(2) : null,
                'reschedule_reason' => $isRescheduledExample ? 'Demo reschedule due to interviewer availability.' : null,
                'instructions' => 'Online interview through Google Meet.',
                'notify_beneficiaries' => true,
                'created_at' => now()->subDays(14),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedContracts(User $admin, array $applications): void
    {
        $eligible = array_values(array_filter($applications, fn ($application) => in_array($application->status, [
            'assigned',
            'for_contract',
            'contract_signed',
            'deployed',
            'ongoing',
            'completion_review',
            'completed',
        ], true)));

        foreach (array_slice($eligible, 0, 12) as $index => $application) {
            $pending = in_array($application->status, ['assigned', 'for_contract'], true);
            $scheduledAt = now()->addDays($pending ? 4 : -6)->setTime(8 + ($index % 5), 30);

            $this->upsert('contracts', ['application_id' => $application->id], [
                'application_id' => $application->id,
                'contract_date' => $scheduledAt,
                'location' => 'CPESO Office, Capitol Compound, City of San Fernando, Pampanga',
                'notes' => 'Bring valid ID and guardian if required.',
                'status' => $pending ? 'scheduled' : 'completed',
                'result' => $pending ? null : 'signed',
                'schedule_group_id' => 'demo-contract-batch-2026',
                'batch_title' => 'SPES Demo Contract Signing Batch',
                'scheduled_by' => $admin->id,
                'end_at' => (clone $scheduledAt)->addHour(),
                'instructions' => 'Face-to-face contract signing at CPESO.',
                'notify_beneficiaries' => true,
                'created_at' => now()->subDays(12),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedAttendance(array $applications): void
    {
        $eligible = array_values(array_filter($applications, fn ($application) => in_array($application->status, [
            'deployed',
            'ongoing',
            'completion_review',
            'completed',
        ], true) && $application->job && $application->employer));

        for ($i = 0; $i < 45; $i++) {
            $application = $eligible[$i % count($eligible)];
            $workDate = now()->subDays(20 - ($i % 15))->toDateString();
            $reviewStatus = $application->status === 'deployed' && $i % 3 === 0 ? 'pending' : 'approved';

            $this->upsert('attendances', [
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
                'date' => $workDate,
            ], [
                'beneficiary_id' => $application->beneficiary_id,
                'employer_id' => $application->employer->id,
                'application_id' => $application->id,
                'job_id' => $application->job->id,
                'date' => $workDate,
                'status' => 'present',
                'remarks' => 'Demo DTR entry.',
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'notes' => json_encode(['in' => 'demo/dtr/time-in.jpg', 'out' => 'demo/dtr/time-out.jpg']),
                'review_status' => $reviewStatus,
                'review_remarks' => $reviewStatus === 'approved' ? 'DTR reviewed for demo.' : null,
                'reviewed_by' => $reviewStatus === 'approved' ? $application->employer->user_id : null,
                'reviewed_at' => $reviewStatus === 'approved' ? now()->subDay() : null,
                'created_at' => now()->subDays(20 - ($i % 15)),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedWorkOutputs(array $applications): void
    {
        $eligible = array_values(array_filter($applications, fn ($application) => in_array($application->status, [
            'assigned',
            'contract_signed',
            'deployed',
            'ongoing',
            'completion_review',
            'completed',
        ], true) && $application->job && $application->employer));

        for ($i = 0; $i < 45; $i++) {
            $application = $eligible[$i % count($eligible)];
            $workDate = now()->subDays(20 - ($i % 15))->toDateString();
            $status = match ($application->status) {
                'assigned', 'contract_signed' => 'submitted',
                'deployed' => $i % 4 === 0 ? 'needs_correction' : 'submitted',
                default => 'approved',
            };

            $this->upsert('work_outputs', [
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
                'work_date' => $workDate,
            ], [
                'employer_id' => $application->employer->id,
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
                'job_listing_id' => $application->job->id,
                'work_date' => $workDate,
                'title' => 'Daily Accomplishment Report - ' . now()->parse($workDate)->format('M d'),
                'description' => 'Demo accomplishment report for SPES workday.',
                'accomplishments' => 'Assisted office staff with encoding, filing, client assistance, and daily assigned tasks.',
                'hours_worked' => 8.00,
                'status' => $status,
                'submitted_by' => $application->beneficiary->user_id,
                'reviewed_by' => in_array($status, ['approved', 'rejected', 'needs_correction'], true) ? $application->employer->user_id : null,
                'reviewed_at' => in_array($status, ['approved', 'rejected', 'needs_correction'], true) ? now()->subDay() : null,
                'review_remarks' => $status === 'needs_correction' ? 'Please add more detail about completed tasks.' : ($status === 'approved' ? 'Daily report approved for demo.' : null),
                'file_path' => 'work_outputs/demo-daily-report.pdf',
                'original_name' => 'demo-daily-report.pdf',
                'created_at' => now()->subDays(20 - ($i % 15)),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedEmployerRatings(array $applications): void
    {
        $eligible = array_values(array_filter($applications, fn ($application) => in_array($application->status, [
            'ongoing',
            'completion_review',
            'completed',
        ], true) && $application->employer));

        for ($i = 0; $i < 10; $i++) {
            $application = $eligible[$i % count($eligible)];
            $this->upsert('employer_ratings', [
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
            ], [
                'employer_id' => $application->employer->id,
                'beneficiary_id' => $application->beneficiary_id,
                'application_id' => $application->id,
                'punctuality' => 5,
                'work_attitude' => 5,
                'attitude' => 5,
                'output_quality' => 4,
                'work_quality' => 4,
                'communication' => 5,
                'overall' => 5,
                'comment' => 'Reliable and cooperative SPES beneficiary.',
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedAnnouncements(User $admin): void
    {
        $announcements = [
            ['SPES Orientation Schedule', 'All beneficiaries should monitor their exam and interview schedules.', 'beneficiary'],
            ['Daily Report Reminder', 'Submit your Daily Accomplishment Report after DTR submission.', 'beneficiary'],
            ['Employer DTR Review Reminder', 'Please review attendance and daily reports from assigned beneficiaries.', 'employer'],
            ['Completion Review Advisory', 'Completion submissions are reviewed by CPESO before certificates are released.', 'all'],
            ['PESO Interview Task Update', 'Assigned interviewers should check their interview queue daily.', 'peso'],
            ['SPES Monitoring Notice', 'Demo monitoring data is ready for workflow testing.', 'all'],
        ];

        foreach ($announcements as [$title, $content, $targetRole]) {
            $this->upsert('announcements', ['title' => $title], [
                'title' => $title,
                'content' => $content,
                'target_role' => $targetRole,
                'created_by' => $admin->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedReports(array $employers): void
    {
        for ($i = 0; $i < 6; $i++) {
            $employer = $employers[$i % count($employers)];
            $employerReference = $this->reportsEmployerReferenceUsesUsers() ? $employer->user_id : $employer->id;

            $this->upsert('reports', [
                'employer_id' => $employerReference,
                'title' => 'SPES Demo Monitoring Report ' . ($i + 1),
            ], [
                'employer_id' => $employerReference,
                'title' => 'SPES Demo Monitoring Report ' . ($i + 1),
                'body' => 'Demo report for SPES deployment, attendance, and completion monitoring.',
                'file_path' => 'reports/demo-monitoring-report.pdf',
                'report_type' => $i % 2 === 0 ? 'employer' : 'monitoring',
                'report_details' => 'Demo report generated by SpesDemoSeeder.',
                'report_date' => now()->subDays($i)->toDateString(),
                'created_at' => now()->subDays($i + 1),
                'updated_at' => now(),
            ]);
        }
    }

    private function beneficiaryDocuments(string $type): array
    {
        $documents = [
            'birth_certificate' => 'demo/beneficiaries/birth-certificate.pdf',
            'income_proof' => 'demo/beneficiaries/income-proof.pdf',
            'barangay_certificate' => 'demo/beneficiaries/barangay-certificate.pdf',
        ];

        if ($type === 'student') {
            $documents['school_record'] = 'demo/beneficiaries/school-record.pdf';
            $documents['school_id'] = 'demo/beneficiaries/school-id.pdf';
        }

        if ($type === 'osy') {
            $documents['osy_certificate'] = 'demo/beneficiaries/osy-certificate.pdf';
        }

        if ($type === 'dependent') {
            $documents['displacement_certificate'] = 'demo/beneficiaries/displacement-certificate.pdf';
            $documents['termination_notice'] = 'demo/beneficiaries/termination-notice.pdf';
        }

        return $documents;
    }

    private function requiresAssignment(string $status): bool
    {
        return in_array($status, ['assigned', 'for_contract', 'contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed'], true);
    }

    private function employmentStatus(string $status): string
    {
        return match ($status) {
            'assigned', 'for_contract', 'contract_signed' => 'assigned',
            'deployed', 'ongoing', 'completion_review' => 'employed',
            'completed' => 'completed',
            'rejected' => 'unemployed',
            default => 'unassigned',
        };
    }

    private function findEmployerForJob(array $employers, int $employerId): ?Employer
    {
        foreach ($employers as $employer) {
            if ((int) $employer->id === $employerId) {
                return $employer;
            }
        }

        return null;
    }

    private function reportsEmployerReferenceUsesUsers(): bool
    {
        $constraint = DB::selectOne(
            "select referenced_table_name from information_schema.key_column_usage where table_schema = database() and table_name = 'reports' and column_name = 'employer_id' and referenced_table_name is not null limit 1"
        );

        return ($constraint?->referenced_table_name ?? null) === 'users';
    }

    private function upsert(string $table, array $where, array $data): object
    {
        if (! Schema::hasTable($table)) {
            $this->skippedFields[$table]['__table_missing__'] = true;
            return (object) [];
        }

        $where = $this->filter($table, $where);
        $data = $this->filter($table, $data);

        if ($where === []) {
            $this->skippedFields[$table]['__missing_lookup_columns__'] = true;
            return (object) [];
        }

        DB::table($table)->updateOrInsert($where, $data);
        $this->bump($table);

        return DB::table($table)->where($where)->first();
    }

    private function filter(string $table, array $data): array
    {
        $columns = $this->columns[$table] ?? [];
        $filtered = [];

        foreach ($data as $key => $value) {
            if (isset($columns[$key])) {
                $filtered[$key] = $value;
                continue;
            }

            $this->skippedFields[$table][$key] = true;
        }

        return $filtered;
    }

    private function hasColumn(string $table, string $column): bool
    {
        return isset($this->columns[$table][$column]);
    }

    private function bump(string $key): void
    {
        $this->counts[$key] = ($this->counts[$key] ?? 0) + 1;
    }

    private function printSummary(): void
    {
        $this->command?->info('SPES demo data seeded.');
        $this->command?->line('Accounts:');
        $this->command?->line('  Admin: admin@spes.test / password');
        $this->command?->line('  PESO: peso1@spes.test, peso2@spes.test, peso3@spes.test / password');
        $this->command?->line('  Employers: employer1@spes.test, employer2@spes.test, employer3@spes.test / password');
        $this->command?->line('  Beneficiary examples: student1@spes.test, osy1@spes.test, ddw1@spes.test / password');
        $this->command?->line('Upserted record counts: ' . json_encode($this->counts, JSON_PRETTY_PRINT));

        if ($this->skippedFields) {
            $this->command?->warn('Skipped fields due to missing columns: ' . json_encode(array_map('array_keys', $this->skippedFields), JSON_PRETTY_PRINT));
        }
    }
}
