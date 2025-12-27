<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\JobListing;
use App\Models\Interview;
use Spatie\Permission\Models\Role;

class PESOFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_peso_can_assign_beneficiary()
    {
        Role::create(['name' => 'PESO']);
        Role::create(['name' => 'Beneficiary']);

        $peso = User::factory()->create();
        $peso->assignRole('PESO');

        $beneficiary = User::factory()->create();
        $beneficiary->assignRole('Beneficiary');

        // Initialize session for CSRF token
        $this->get('/');

        $response = $this->actingAs($peso)
            ->post('/peso/assign-beneficiary', [
                '_token' => csrf_token(),
                'beneficiary_id' => $beneficiary->id,
                'notes' => 'Assigned for test',
            ]);

        $response->assertStatus(200);
    }

    public function test_peso_can_schedule_interview()
    {
        Role::create(['name' => 'PESO']);
        Role::create(['name' => 'Employer']);
        Role::create(['name' => 'Beneficiary']);

        $peso = User::factory()->create();
        $peso->assignRole('PESO');

        $employer = User::factory()->create();
        $employer->assignRole('Employer');

        $beneficiary = User::factory()->create();
        $beneficiary->assignRole('Beneficiary');

        $job = JobListing::factory()->create(['employer_id' => $employer->id]);

        // Initialize session for CSRF token
        $this->get('/');

        $response = $this->actingAs($peso)->post('/peso/schedule-interview', [
            '_token' => csrf_token(),
            'job_id' => $job->id,
            'beneficiary_id' => $beneficiary->id,
            'scheduled_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertStatus(200);
    }
}
