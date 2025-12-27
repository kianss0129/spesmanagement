<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_roles_index()
    {
        Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get('/admin/roles');

        $response->assertStatus(200);
        $response->assertSee('users'); // Inertia payload has "users" prop
    }

    public function test_non_admin_cannot_view_roles_index()
    {
        Role::create(['name' => 'Beneficiary']);
        $user = User::factory()->create();
        $user->assignRole('Beneficiary');

        $response = $this->actingAs($user)->get('/admin/roles');

        $response->assertStatus(403);
    }

    public function test_admin_can_assign_role_to_user()
    {
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Beneficiary']);

        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $target = User::factory()->create();

        // Initialize session so we have a CSRF token available
        $this->get('/');

        $response = $this->actingAs($admin)->post('/admin/roles/assign', [
            '_token' => csrf_token(),
            'user_id' => $target->id,
            'role' => 'Beneficiary',
        ]);

        $response->assertStatus(302);
        $this->assertTrue($target->fresh()->hasRole('Beneficiary'));
    }

    public function test_non_admin_cannot_assign_roles()
    {
        Role::create(['name' => 'Beneficiary']);

        $user = User::factory()->create();
        $user->assignRole('Beneficiary');

        $target = User::factory()->create();

        // Initialize session so we have a CSRF token available
        $this->get('/');

        $response = $this->actingAs($user)->post('/admin/roles/assign', [
            '_token' => csrf_token(),
            'user_id' => $target->id,
            'role' => 'Beneficiary',
        ]);

        $response->assertStatus(403);
    }
}
