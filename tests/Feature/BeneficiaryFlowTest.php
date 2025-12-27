<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JobListing;
use Spatie\Permission\Models\Role;

class BeneficiaryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_beneficiary_can_list_jobs_and_apply()
    {
        Role::create(['name' => 'Beneficiary']);

        $beneficiary = User::factory()->create();
        $beneficiary->assignRole('Beneficiary');

        $job = JobListing::factory()->create();

        $this->actingAs($beneficiary)
            ->get('/beneficiary/jobs')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $job->id]);

        // Initialize session for CSRF token
        $this->get('/');

        $response = $this->actingAs($beneficiary)
            ->post('/beneficiary/applications', [
                '_token' => csrf_token(),
                'job_id' => $job->id,
                'cover_letter' => 'Test',
            ]);

        $response->assertStatus(200);
    }

    public function test_beneficiary_can_upload_documents_and_submit_attendance()
    {
        Role::create(['name' => 'Beneficiary']);

        $beneficiary = User::factory()->create();
        $beneficiary->assignRole('Beneficiary');

        // Simulate upload (no file system test here)
        // Initialize session for CSRF token
        $this->get('/');

        $response = $this->actingAs($beneficiary)
            ->post('/beneficiary/upload-documents', [
                '_token' => csrf_token(),
                'documents' => [],
            ]);

        $response->assertStatus(200);

        $this->actingAs($beneficiary)
            ->post('/beneficiary/attendance', [
                '_token' => csrf_token(),
                'date' => now()->toDateString(),
                'present' => true,
            ])->assertStatus(200);
    }
}
