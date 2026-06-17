<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Attendance;
use App\Models\Beneficiary;
use App\Models\Contract;
use App\Models\Employer;
use App\Models\EmployerRating;
use App\Models\Exam;
use App\Models\Interview;
use App\Models\JobListing;
use App\Models\Skill;
use App\Models\User;
use App\Models\WorkOutput;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoWorkflowSeeder extends Seeder
{
    private int $countPerStatus = 3;
    private string $password;
    private array $employers = [];
    private array $jobs = [];
    private array $skills = [];

    public function run(): void
    {
        $this->password = Hash::make('password123');

        $this->createRoles();
        $this->createSkills();
        $this->createEmployers();
        $this->createJobListings();
        $this->seedBeneficiaries();

        $this->command->info('');
        $this->command->info('=== DEMO WORKFLOW SEEDER COMPLETE ===');
        $this->command->info("Password for all demo accounts: password123");
        $this->command->info("Employers: demo-employer1@spes.test, demo-employer2@spes.test, demo-employer3@spes.test");
        $this->command->info("Beneficiaries: demo-{status}-{n}@spes.test (3 per status × 18 statuses = 54)");
        $this->command->info('');
    }

    private function createRoles(): void
    {
        Role::firstOrCreate(['name' => 'Beneficiary']);
        Role::firstOrCreate(['name' => 'Employer']);
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'PESO Admin']);
        Role::firstOrCreate(['name' => 'PESO']);
    }

    private function createSkills(): void
    {
        $skillNames = ['Computer Literacy', 'Communication', 'Teamwork', 'Data Entry', 'Customer Service', 'Filing', 'Organizational Skills'];

        foreach ($skillNames as $name) {
            $this->skills[] = Skill::firstOrCreate(['name' => $name], [
                'category' => 'General',
                'description' => "Demo skill: {$name}",
            ]);
        }
    }

    private function createEmployers(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $email = "demo-employer{$i}@spes.test";
            $user = User::firstOrCreate(['email' => $email], [
                'name' => "Demo Employer {$i}",
                'password' => $this->password,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Employer');

            $employer = Employer::firstOrCreate(['user_id' => $user->id], [
                'company_name' => "Demo Company {$i}",
                'email' => $email,
                'phone' => '9' . str_pad($i, 9, '0', STR_PAD_LEFT),
                'contact_person' => "Contact Person {$i}",
                'address' => "San Fernando, Pampanga",
                'approved' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'status' => 'active',
                'onboarding_completed_at' => now(),
            ]);

            $this->employers[] = $employer;
        }
    }

    private function createJobListings(): void
    {
        $titles = ['Office Assistant', 'Data Encoder', 'Filing Clerk', 'Customer Service Aide', 'Library Assistant', 'IT Support Trainee'];

        foreach ($this->employers as $idx => $employer) {
            for ($j = 0; $j < 2; $j++) {
                $title = $titles[($idx * 2 + $j) % count($titles)];
                $job = JobListing::firstOrCreate([
                    'employer_id' => $employer->id,
                    'title' => $title . ' (Demo)',
                ], [
                    'description' => "Demo job listing for testing: {$title}",
                    'location' => 'San Fernando, Pampanga',
                    'type' => 'Full-time',
                    'slots' => 5,
                    'closing_date' => now()->addMonths(3)->toDateString(),
                ]);

                // Attach skills
                $skillIds = collect($this->skills)->random(3)->pluck('id')->toArray();
                $job->skills()->syncWithoutDetaching($skillIds);

                $this->jobs[] = $job;
            }
        }
    }

    private function seedBeneficiaries(): void
    {
        $statuses = [
            'registered_not_completed',
            'applied',
            'needs_correction',
            'screening',
            'for_exam',
            'exam_passed',
            'for_interview',
            'interview_passed',
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

        foreach ($statuses as $status) {
            for ($n = 1; $n <= $this->countPerStatus; $n++) {
                $this->createBeneficiaryForStatus($status, $n);
            }
        }
    }

    private function createBeneficiaryForStatus(string $status, int $n): void
    {
        $slug = str_replace('_', '-', $status);
        $email = "demo-{$slug}-{$n}@spes.test";
        $firstName = ucfirst(explode('_', $status)[0]);
        $lastName = "Demo{$n}";

        // Create user
        $user = User::firstOrCreate(['email' => $email], [
            'name' => "{$firstName} {$lastName}",
            'password' => $this->password,
            'email_verified_at' => now(),
            'beneficiary_type' => 'student',
        ]);
        $user->assignRole('Beneficiary');

        // For registered_not_completed: only user + empty beneficiary, no application
        if ($status === 'registered_not_completed') {
            Beneficiary::firstOrCreate(['user_id' => $user->id], [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'approval_status' => 'pending',
                'approved' => false,
                'status' => 'pending',
                'category' => 'student',
            ]);
            return;
        }

        // Create beneficiary with full onboarding data
        $isApproved = in_array($status, ['screening', 'for_exam', 'exam_passed', 'for_interview', 'interview_passed', 'qualified', 'approved', 'assigned', 'for_contract', 'contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed']);
        $isNeedsCorrection = $status === 'needs_correction';

        $beneficiary = Beneficiary::firstOrCreate(['user_id' => $user->id], [
            'first_name' => $firstName,
            'middle_name' => 'M',
            'last_name' => $lastName,
            'email' => $email,
            'phone' => '+639' . str_pad(rand(100000000, 999999999), 9, '0'),
            'contact_number' => '+639' . str_pad(rand(100000000, 999999999), 9, '0'),
            'birth_date' => '2004-03-15',
            'age' => 22,
            'sex' => 'Male',
            'civil_status' => 'Single',
            'place_of_birth' => 'San Fernando, Pampanga',
            'citizenship' => 'Filipino',
            'category' => 'student',
            'present_address' => 'Purok 3',
            'barangay' => 'Sindalan',
            'city' => 'San Fernando',
            'province' => 'Pampanga',
            'father_name' => 'Father ' . $lastName,
            'father_contact' => '+639111111111',
            'father_occupation' => 'Driver',
            'mother_name' => 'Mother ' . $lastName,
            'mother_contact' => '+639222222222',
            'mother_occupation' => 'Homemaker',
            'family_income' => 'Below ₱5,000',
            'school_name' => 'City College of San Fernando Pampanga',
            'school_address' => 'San Fernando, Pampanga',
            'education_level' => 'College',
            'school_year' => '2025-2026',
            'year_level' => '2nd Year',
            'course' => 'BSIT',
            'previous_spes' => false,
            'approval_status' => $isApproved ? 'approved' : ($isNeedsCorrection ? 'needs_correction' : 'pending'),
            'approved' => $isApproved,
            'approved_at' => $isApproved ? now() : null,
            'status' => $isApproved ? 'approved' : 'pending',
            'draft_status' => 'submitted',
            'onboarding_step' => 5,
            'completion_percentage' => 100,
            'completed_steps' => [1, 2, 3, 4, 5],
            'onboarding_completed_at' => now()->subDays(10),
            'submitted_at' => now()->subDays(10),
            'documents' => [
                'valid_id' => ['path' => 'documents/demo/valid_id.jpg', 'name' => 'Valid_ID.jpg', 'status' => 'uploaded', 'uploaded_at' => now()->subDays(10)->toIso8601String()],
                'barangay_certificate' => ['path' => 'documents/demo/barangay.jpg', 'name' => 'Barangay_Cert.jpg', 'status' => 'uploaded', 'uploaded_at' => now()->subDays(10)->toIso8601String()],
                'school_enrollment' => ['path' => 'documents/demo/enrollment.jpg', 'name' => 'Enrollment.jpg', 'status' => 'uploaded', 'uploaded_at' => now()->subDays(10)->toIso8601String()],
            ],
            'rejection_reason' => $isNeedsCorrection ? 'Please upload a clearer Valid ID photo.' : null,
        ]);

        // Attach skills
        $skillIds = collect($this->skills)->random(min(4, count($this->skills)))->pluck('id')->toArray();
        $beneficiary->skills()->syncWithoutDetaching($skillIds);

        // Create application if status != registered_not_completed
        if ($status === 'rejected') {
            $appStatus = 'rejected';
        } else {
            $appStatus = $status;
        }

        $job = $this->jobs[array_rand($this->jobs)];
        $employer = $this->employers[array_rand($this->employers)];
        $needsJob = in_array($status, ['assigned', 'for_contract', 'contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed']);

        $application = Application::firstOrCreate(
            ['beneficiary_id' => $beneficiary->id],
            [
                'job_listing_id' => $needsJob ? $job->id : null,
                'status' => $appStatus,
                'employer_acknowledged_at' => in_array($status, ['deployed', 'ongoing', 'completion_review', 'completed']) ? now()->subDays(5) : null,
                'employer_acknowledged_by' => in_array($status, ['deployed', 'ongoing', 'completion_review', 'completed']) ? $employer->user_id : null,
            ]
        );

        // Ensure status is correct (in case firstOrCreate found existing)
        if ($application->status !== $appStatus) {
            $application->update(['status' => $appStatus, 'job_listing_id' => $needsJob ? $job->id : $application->job_listing_id]);
        }

        // Create related records based on status progression
        $this->createRelatedRecords($status, $beneficiary, $application, $job, $employer);
    }

    private function createRelatedRecords(string $status, Beneficiary $beneficiary, Application $application, JobListing $job, Employer $employer): void
    {
        $statusOrder = ['applied', 'needs_correction', 'screening', 'for_exam', 'exam_passed', 'for_interview', 'interview_passed', 'qualified', 'approved', 'assigned', 'for_contract', 'contract_signed', 'deployed', 'ongoing', 'completion_review', 'completed'];
        $currentIndex = array_search($status, $statusOrder);
        if ($currentIndex === false) return;

        // Exam (for_exam and beyond)
        if ($currentIndex >= array_search('for_exam', $statusOrder)) {
            $examResult = $currentIndex >= array_search('exam_passed', $statusOrder) ? 'passed' : null;
            Exam::firstOrCreate(['application_id' => $application->id], [
                'exam_date' => now()->subDays(8),
                'location' => 'PESO Office, San Fernando',
                'status' => $examResult ? 'completed' : 'scheduled',
                'result' => $examResult,
                'scheduled_by' => 1,
                'schedule_group_id' => (string) Str::uuid(),
            ]);
        }

        // Interview (for_interview and beyond)
        if ($currentIndex >= array_search('for_interview', $statusOrder)) {
            $interviewResult = $currentIndex >= array_search('interview_passed', $statusOrder) ? 'passed' : 'pending';
            $pesoUser = User::role('PESO')->first();
            Interview::firstOrCreate(['application_id' => $application->id], [
                'job_listing_id' => $job->id,
                'employer_id' => $employer->id,
                'beneficiary_id' => $beneficiary->id,
                'scheduled_at' => now()->subDays(6),
                'end_at' => now()->subDays(6)->addHour(),
                'meet_link' => 'https://meet.google.com/demo-' . Str::random(8),
                'scheduled_by' => 1,
                'interviewer_id' => $pesoUser?->id ?? 3,
                'status' => $interviewResult === 'passed' ? 'completed' : 'scheduled',
                'result' => $interviewResult,
                'remarks' => $interviewResult === 'passed' ? 'Good communication skills, recommended.' : null,
                'evaluated_at' => $interviewResult === 'passed' ? now()->subDays(6) : null,
                'schedule_group_id' => (string) Str::uuid(),
            ]);
        }

        // Contract (for_contract and beyond)
        if ($currentIndex >= array_search('for_contract', $statusOrder)) {
            $contractResult = $currentIndex >= array_search('contract_signed', $statusOrder) ? 'signed' : null;
            Contract::firstOrCreate(['application_id' => $application->id], [
                'contract_date' => now()->subDays(4),
                'location' => 'PESO Office, San Fernando',
                'status' => $contractResult ? 'completed' : 'scheduled',
                'result' => $contractResult,
                'scheduled_by' => 1,
                'schedule_group_id' => (string) Str::uuid(),
            ]);
        }

        // Attendance (deployed and beyond)
        if ($currentIndex >= array_search('deployed', $statusOrder)) {
            for ($day = 1; $day <= 3; $day++) {
                Attendance::firstOrCreate([
                    'beneficiary_id' => $beneficiary->id,
                    'date' => now()->subDays($day)->toDateString(),
                ], [
                    'employer_id' => $employer->id,
                    'application_id' => $application->id,
                    'time_in' => '08:00',
                    'time_out' => '17:00',
                    'status' => 'present',
                    'notes' => json_encode(['in' => null, 'out' => null]),
                ]);
            }
        }

        // Work Outputs (ongoing and beyond)
        if ($currentIndex >= array_search('ongoing', $statusOrder)) {
            for ($day = 1; $day <= 2; $day++) {
                WorkOutput::firstOrCreate([
                    'beneficiary_id' => $beneficiary->id,
                    'work_date' => now()->subDays($day)->toDateString(),
                ], [
                    'employer_id' => $employer->id,
                    'application_id' => $application->id,
                    'job_listing_id' => $job->id,
                    'title' => "Daily Report Day {$day}",
                    'accomplishments' => "Completed filing tasks and data entry for Day {$day}.",
                    'hours_worked' => 8,
                    'status' => $currentIndex >= array_search('completion_review', $statusOrder) ? 'approved' : 'submitted',
                    'submitted_by' => $beneficiary->user_id,
                ]);
            }
        }

        // Employer Rating (completion_review and beyond)
        if ($currentIndex >= array_search('completion_review', $statusOrder)) {
            EmployerRating::firstOrCreate([
                'beneficiary_id' => $beneficiary->id,
                'application_id' => $application->id,
            ], [
                'employer_id' => $employer->id,
                'punctuality' => rand(4, 5),
                'output_quality' => rand(4, 5),
                'work_attitude' => rand(4, 5),
                'communication' => rand(3, 5),
                'overall' => rand(4, 5),
                'comment' => 'Good performance during SPES period.',
            ]);
        }

        // Update beneficiary employer/job link for assigned+
        if ($currentIndex >= array_search('assigned', $statusOrder)) {
            $beneficiary->update([
                'employer_id' => $employer->id,
                'job_id' => $job->id,
                'employment_status' => $currentIndex >= array_search('completed', $statusOrder) ? 'completed' : 'assigned',
            ]);
        }

        // Mark completed
        if ($status === 'completed') {
            $beneficiary->update(['completed_at' => now()->subDay()]);
            $application->update(['certificate_path' => 'certificates/demo-certificate.pdf']);
        }
    }
}
