<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_stats()
    {
        // Prepare roles and admin
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'PESO']);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        // Create a couple of users and assign peso role to one
        $worker = User::factory()->create();
        $worker->assignRole('PESO');

        User::factory()->count(3)->create();

        // Create some activity_log entries if table exists
        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')->insert([
                ['description' => 'PESO assigned beneficiary 1', 'causer_id' => $worker->id, 'created_at' => now(), 'updated_at' => now()],
                ['description' => 'PESO assigned beneficiary 2', 'causer_id' => $worker->id, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $response = $this->actingAs($admin)->getJson('/admin/stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'users', 'beneficiaries', 'employers', 'peso_users', 'latest_users', 'chart_dates', 'users_growth', 'applications_by_peso', 'recent_activity', 'assigned_beneficiaries', 'upcoming_interviews', 'pending_applications'
        ]);

        // peso_users should be at least 1
        $this->assertTrue($response->json('peso_users') >= 1);

        // New counts should be integers
        $this->assertIsInt($response->json('assigned_beneficiaries'));
        $this->assertIsInt($response->json('upcoming_interviews'));
        $this->assertIsInt($response->json('pending_applications'));
    }

    public function test_admin_can_export_users_csv()
    {
        Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        User::factory()->create(['name' => 'Alice', 'email' => 'alice@example.com']);

        $response = $this->actingAs($admin)->get('/admin/export-users');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        // Streamed responses may not capture content in the test client; assert headers are correct
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));

    }
}
