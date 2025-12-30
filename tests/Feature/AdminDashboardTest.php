<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_manage_roles_link()
    {
        Role::create(['name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->assignRole('Admin');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);

        // Extract Inertia payload from data-page attribute and assert the user has Admin role
        $content = $response->getContent();
        preg_match('/<div id="app" data-page="(?P<data>.+)"><\/div>/s', $content, $matches);
        $this->assertArrayHasKey('data', $matches);
        $payload = json_decode(html_entity_decode($matches['data']), true);
        $this->assertEquals('Admin/Dashboard', $payload['component']);
        $roles = $payload['props']['auth']['user']['roles'] ?? [];
        $this->assertNotEmpty($roles);
        $this->assertTrue(collect($roles)->contains(fn($r) => $r['name'] === 'Admin'));


    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }
}
