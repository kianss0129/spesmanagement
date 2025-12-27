<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JobListing;
use App\Models\Application as JobApplication;
use Spatie\Permission\Models\Role;

class EmployerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_view_applicants_and_choose()
    {
        Role::create(['name' => 'Employer']);
        Role::create(['name' => 'Beneficiary']);

        $employer = User::factory()->create();
        $employer->assignRole('Employer');

        $beneficiary = User::factory()->create();
        $beneficiary->assignRole('Beneficiary');

        $job = JobListing::factory()->create(['employer_id' => $employer->id]);

        // Create application record directly (no factory for Application)
        $app = \App\Models\Application::create([
            'job_listing_id' => $job->id,
            'beneficiary_id' => $beneficiary->id,
            'status' => 'applied',
        ]);

        $this->actingAs($employer)
            ->get("/employer/jobs/{$job->id}/applicants")
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $app->id]);

        // Initialize session for CSRF token
        $this->get('/');

        // Choose applicant
        $this->post("/employer/jobs/{$job->id}/choose/{$app->id}", ['_token' => csrf_token()])
            ->assertStatus(200);

        $this->assertDatabaseHas('applications', [
            'id' => $app->id,
            'status' => 'chosen',
        ]);
    }

    public function test_employer_can_submit_attendance_and_reports()
    {
        Role::create(['name' => 'Employer']);
        $employer = User::factory()->create();
        $employer->assignRole('Employer');

        $payload = ['date' => now()->toDateString(), 'records' => []];

        // Initialize session for CSRF token
        $this->get('/');

        $this->actingAs($employer)
            ->post('/employer/attendance/mark', array_merge($payload, ['_token' => csrf_token()]))
            ->assertStatus(200);

        $this->actingAs($employer)
            ->post('/employer/reports', array_merge(['title' => 'Test', 'content' => 'ok'], ['_token' => csrf_token()]))
            ->assertStatus(200);
    }
}
