<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_lists_active_and_inactive_users(): void
    {
        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonPath('total', 7);

        $this->getJson('/api/users?status=inactive')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.is_active', false);

        $this->getJson('/api/users?status=active')->assertJsonPath('total', 5);
    }

    public function test_shows_one_user_and_returns_404_when_missing(): void
    {
        $this->getJson('/api/users/1')
            ->assertOk()
            ->assertJsonPath('email', 'admin@halcon.com')
            ->assertJsonPath('role.name', 'Admin')
            ->assertJsonMissingPath('password');

        $this->getJson('/api/users/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'User not found.');
    }

    public function test_creates_updates_and_deactivates_a_user(): void
    {
        $id = $this->postJson('/api/users', [
            'name' => 'Pedro Gómez',
            'email' => 'pedro.gomez@halcon.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 2,
        ])->assertCreated()
            ->assertJsonPath('is_active', true)
            ->json('id');

        $this->patchJson("/api/users/{$id}", ['name' => 'Pedro Gómez Ruiz'])
            ->assertOk()
            ->assertJsonPath('name', 'Pedro Gómez Ruiz');

        $this->patchJson("/api/users/{$id}/deactivate")
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->patchJson("/api/users/{$id}/activate")
            ->assertOk()
            ->assertJsonPath('data.is_active', true);
    }

    public function test_validation_errors_are_json_without_accept_header(): void
    {
        $this->post('/api/users', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role_id']);
    }
}
