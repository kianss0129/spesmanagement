<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_beneficiary_register_page_renders()
    {
        $response = $this->get('/register/beneficiary');

        $response->assertStatus(200);
    }

    public function test_beneficiary_can_register_via_post()
    {
        $response = $this->post('/register/beneficiary', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('beneficiary.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        // Optionally follow and assert 200 on the dashboard
        $follow = $this->followingRedirects()->post('/register/beneficiary', [
            'name' => 'Another User',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $follow->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'another@example.com']);
    }

    public function test_registration_validation_redirects_to_get_route()
    {
        $response = $this->post('/register/beneficiary', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'nope',
        ]);

        $response->assertRedirect(route('register.beneficiary'));
        $response->assertSessionHasErrors(['name','email','password']);
    }
}
