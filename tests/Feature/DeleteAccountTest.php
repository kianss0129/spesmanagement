<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accounts_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        // Seed a test user and authenticate
        $this->actingAs($user = User::factory()->create());

        // Ensure session and CSRF are initialized (Jetstream expects a session token)
        $this->get('/');

        // Send a POST request simulating the account deletion form (POST with _method=DELETE)
        $response = $this->post('/user', [
            '_method' => 'DELETE',
            '_token' => csrf_token(),
            'password' => 'password',
        ]);

        $response->assertStatus(302); // Redirect on success

        // Assert the user is removed from the database
        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_correct_password_must_be_provided_before_account_can_be_deleted(): void
    {
        if (! Features::hasAccountDeletionFeatures()) {
            $this->markTestSkipped('Account deletion is not enabled.');
        }

        // Seed a test user and authenticate
        $this->actingAs($user = User::factory()->create());

        // Initialize session for CSRF token
        $this->get('/');

        // Attempt deletion with incorrect password — should not delete the user
        $response = $this->post('/user', [
            '_method' => 'DELETE',
            '_token' => csrf_token(),
            'password' => 'wrong-password',
        ]);

        // Expect validation redirect with errors
        $response->assertStatus(302);

        $this->assertNotNull($user->fresh());
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
