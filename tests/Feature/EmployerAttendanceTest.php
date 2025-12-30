<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Beneficiary;
use Spatie\Permission\Models\Role;

class EmployerAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_attendance_submission_saves_record()
    {
        Role::create(['name' => 'Employer']);

        $employer = User::factory()->create();
        $employer->assignRole('Employer');

        $beneficiary = Beneficiary::factory()->create();

        // Initialize session for CSRF token
        $this->get('/');

        $payload = [
            'beneficiary_id' => $beneficiary->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00:00',
            'time_out' => '17:00:00',
            'notes' => 'Single record test',
            '_token' => csrf_token(),
        ];

        $this->actingAs($employer)
            ->post('/employer/attendance/mark', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Attendance submitted']);

        $this->assertDatabaseHas('attendances', [
            'beneficiary_id' => $beneficiary->id,
            'employer_id' => $employer->id,
            'date' => now()->toDateString(),
            'notes' => 'Single record test',
        ]);
    }

    public function test_batch_attendance_submission_saves_multiple_records()
    {
        Role::create(['name' => 'Employer']);

        $employer = User::factory()->create();
        $employer->assignRole('Employer');

        $b1 = Beneficiary::factory()->create();
        $b2 = Beneficiary::factory()->create();

        // Initialize session for CSRF token
        $this->get('/');

        $payload = [
            'date' => now()->toDateString(),
            'records' => [
                [
                    'beneficiary_id' => $b1->id,
                    'time_in' => '08:05:00',
                    'time_out' => '16:10:00',
                    'notes' => 'Batch record 1'
                ],
                [
                    'beneficiary_id' => $b2->id,
                    'time_in' => '08:10:00',
                    'time_out' => '16:15:00',
                    'notes' => 'Batch record 2'
                ],
            ],
            '_token' => csrf_token(),
        ];

        $this->actingAs($employer)
            ->post('/employer/attendance/mark', $payload)
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Attendance submitted']);

        $this->assertDatabaseHas('attendances', [
            'beneficiary_id' => $b1->id,
            'employer_id' => $employer->id,
            'date' => now()->toDateString(),
            'notes' => 'Batch record 1',
        ]);

        $this->assertDatabaseHas('attendances', [
            'beneficiary_id' => $b2->id,
            'employer_id' => $employer->id,
            'date' => now()->toDateString(),
            'notes' => 'Batch record 2',
        ]);

        $this->assertDatabaseCount('attendances', 2);
    }
}
