<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JobListing;
use App\Models\Interview;
use Spatie\Permission\Models\Role;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\Employer;

class PESOFlowTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function peso_can_assign_beneficiary(): void
    {
        Role::create(['name' => 'PESO']);
        Role::create(['name' => 'Employer']);
        Role::create(['name' => 'Beneficiary']);

        $peso = User::factory()->create();
        $peso->assignRole('PESO');

        $employerUser = User::factory()->create();
        $employerUser->assignRole('Employer');

        // Create an Employer record (foreign key target for job_listings)
        $employer = Employer::factory()->create();

        $beneficiary = Beneficiary::factory()->create();

        $job = JobListing::factory()->create([
            'employer_id' => $employer->id,
        ]);

        $application = Application::create([
            'job_listing_id' => $job->id,
            'beneficiary_id' => $beneficiary->id,
            'status' => 'applied',
        ]);

        $this->get('/'); // initialize CSRF

        $response = $this->actingAs($peso)->post('/peso/assign-beneficiary', [
            '_token' => csrf_token(),
            'job_listing_id' => $job->id,
            'beneficiary_id' => $beneficiary->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('job_listings', [
            'id' => $job->id,
            'assigned_beneficiary_id' => $beneficiary->id,
        ]);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'assigned',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function peso_can_schedule_interview(): void
    {
        Role::create(['name' => 'PESO']);
        Role::create(['name' => 'Employer']);
        Role::create(['name' => 'Beneficiary']);

        $peso = User::factory()->create();
        $peso->assignRole('PESO');

        $employerUser = User::factory()->create();
        $employerUser->assignRole('Employer');

        // Create an Employer record (foreign key target for job_listings)
        $employer = Employer::factory()->create();

        $beneficiary = Beneficiary::factory()->create();

        $job = JobListing::factory()->create([
            'employer_id' => $employer->id,
        ]);

        $application = Application::create([
            'job_listing_id' => $job->id,
            'beneficiary_id' => $beneficiary->id,
            'status' => 'applied',
        ]);

        $this->get('/'); // initialize CSRF

        // ✅ FIX IS HERE: "start" instead of "scheduled_at"
        $response = $this->actingAs($peso)->post('/peso/schedule-interview', [
            '_token' => csrf_token(),
            'application_id' => $application->id,
            'start' => now()->addDay()->toDateTimeString(),
            'meet_link' => null,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
        ]);
    }
}
    