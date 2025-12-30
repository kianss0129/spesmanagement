<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JobListing;
use App\Models\Beneficiary;
use App\Models\Application;
use App\Models\Employer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class BeneficiaryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_beneficiary_can_list_jobs_and_apply()
    {
        Role::create(['name' => 'Beneficiary']);
        Role::create(['name' => 'Employer']);

        // Create user and corresponding beneficiary record with same id so controller actions that use
        // auth()->user()->id will align with beneficiary ids used across the DB.
        $user = User::factory()->create();
        $user->assignRole('Beneficiary');

        // Create a beneficiary model that maps to the user id
        $beneficiaryProfile = Beneficiary::factory()->create([
            'id' => $user->id,
            'email' => $user->email,
        ]);

        $employerUser = User::factory()->create();
        $employerUser->assignRole('Employer');

        // Create a real Employer record for job_listings FK
        $employer = Employer::factory()->create();

        // Seed a job listing with salary and employer
        $job = JobListing::factory()->create(['employer_id' => $employer->id, 'salary' => '30000']);

        $this->actingAs($user)
            ->get('/beneficiary/jobs')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $job->id]);

        // Initialize session for CSRF token
        $this->get('/');

        // Apply to the job (controller expects `job_listing_id`)
        $response = $this->actingAs($user)
            ->post('/beneficiary/applications', [
                '_token' => csrf_token(),
                'job_listing_id' => $job->id,
                'cover_letter' => 'Test',
            ]);

        $response->assertStatus(200);

        // Assert application recorded
        $this->assertDatabaseHas('applications', [
            'job_listing_id' => $job->id,
            'beneficiary_id' => $user->id,
            'status' => 'applied'
        ]);
    }

    public function test_beneficiary_can_upload_documents_and_submit_attendance()
    {
        Role::create(['name' => 'Beneficiary']);

        $user = User::factory()->create();
        $user->assignRole('Beneficiary');

        // Create a beneficiary profile that corresponds to user id (so files can be attached if code finds it)
        $beneficiaryProfile = Beneficiary::factory()->create([
            'id' => $user->id,
            'email' => $user->email,
        ]);

        // Use fake storage for uploads
        Storage::fake('public');

        // Initialize session for CSRF token
        $this->get('/');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)
            ->post('/beneficiary/upload-documents', [
                '_token' => csrf_token(),
                'documents' => [$file],
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Documents uploaded']);

        // Ensure file was stored in public disk under documents
        Storage::disk('public')->assertExists('documents/' . $file->hashName());

        // Submit attendance with valid payload
        $this->actingAs($user)
            ->post('/beneficiary/attendance', [
                '_token' => csrf_token(),
                'date' => now()->toDateString(),
                'status' => 'present',
                'remarks' => 'On time'
            ])->assertStatus(200);

        // Attendance should be recorded with beneficiary_id == auth user id
        $this->assertDatabaseHas('attendances', [
            'beneficiary_id' => $user->id,
            'date' => now()->toDateString(),
        ]);
    }
}
