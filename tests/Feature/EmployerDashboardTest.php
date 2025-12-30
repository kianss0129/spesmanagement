<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Employer;
use App\Models\JobListing;
use App\Models\Beneficiary;
use App\Models\Application;
use Spatie\Permission\Models\Role;

class EmployerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_can_fetch_stats()
    {
        // Create employer user and role
        Role::create(['name' => 'Employer']);
        $employerUser = User::factory()->create(['email' => 'employer@example.com']);
        $employerUser->assignRole('Employer');

        // Ensure an Employer record exists for this user and seed a job and applications
        \App\Models\Employer::factory()->create(['id' => $employerUser->id]);
        $job = JobListing::create([ 'employer_id' => $employerUser->id, 'title' => 'Test Job', 'description' => 'T', 'positions' => 1 ]);

        $benef = Beneficiary::factory()->create();
        Application::create(['beneficiary_id' => $benef->id, 'job_listing_id' => $job->id, 'status' => 'applied']);

        $response = $this->actingAs($employerUser)->getJson('/employer/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure(['open_jobs','applicants','upcoming_interviews','pending_ratings','today_attendance','applications_over_time','pipeline']);
    }

    public function test_employer_can_export_applicants_csv()
    {
        Role::create(['name' => 'Employer']);
        $employerUser = User::factory()->create(['email' => 'employer2@example.com']);
        $employerUser->assignRole('Employer');

        \App\Models\Employer::factory()->create(['id' => $employerUser->id]);
        $job = JobListing::create([ 'employer_id' => $employerUser->id, 'title' => 'Export Job', 'description' => 'T', 'positions' => 1 ]);

        $benef = Beneficiary::factory()->create(['first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.com']);
        Application::create(['beneficiary_id' => $benef->id, 'job_listing_id' => $job->id, 'status' => 'applied']);

        $response = $this->actingAs($employerUser)->get('/employer/jobs/'.$job->id.'/export-applicants');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('alice@example.com', $response->getContent());
    }

    public function test_non_owner_cannot_export_applicants()
    {
        Role::create(['name' => 'Employer']);

        $ownerUser = User::factory()->create();
        $ownerUser->assignRole('Employer');
        \App\Models\Employer::factory()->create(['id' => $ownerUser->id]);
        $job = JobListing::create([ 'employer_id' => $ownerUser->id, 'title' => 'Owner Job', 'description' => 'T', 'positions' => 1 ]);

        $otherUser = User::factory()->create();
        $otherUser->assignRole('Employer');

        $response = $this->actingAs($otherUser)->get('/employer/jobs/'.$job->id.'/export-applicants');
        // The controller uses firstOrFail filtering by employer_id, so non-owners get 404
        $response->assertStatus(404);
    }

    public function test_guest_cannot_export_applicants()
    {
        $ownerUser = User::factory()->create();
        \App\Models\Employer::factory()->create(['id' => $ownerUser->id]);
        $job = JobListing::create([ 'employer_id' => $ownerUser->id, 'title' => 'Owner Job', 'description' => 'T', 'positions' => 1 ]);

        $response = $this->get('/employer/jobs/'.$job->id.'/export-applicants');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_employer_cannot_see_other_employer_stats()
    {
        Role::create(['name' => 'Employer']);
        $ownerUser = User::factory()->create(['email' => 'owner@example.com']);
        $ownerUser->assignRole('Employer');
        \App\Models\Employer::factory()->create(['id' => $ownerUser->id]);
        $job = JobListing::create([ 'employer_id' => $ownerUser->id, 'title' => 'Owner Job', 'description' => 'T', 'positions' => 1 ]);
        $benef = Beneficiary::factory()->create();
        Application::create(['beneficiary_id' => $benef->id, 'job_listing_id' => $job->id, 'status' => 'applied']);

        $otherUser = User::factory()->create(['email' => 'other@example.com']);
        $otherUser->assignRole('Employer');

        $response = $this->actingAs($otherUser)->getJson('/employer/stats');

        $response->assertStatus(200);
        $response->assertJsonFragment(['open_jobs' => 0]);
        $response->assertJsonFragment(['applicants' => 0]);
    }
}
