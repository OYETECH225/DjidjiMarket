<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_name_and_email(): void
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => null]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user', [
            'name' => 'New Name',
            'email' => 'new@example.ci',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'New Name');
        $response->assertJsonPath('data.email', 'new@example.ci');

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.ci', $user->email);
    }

    public function test_update_profile_requires_a_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user', ['name' => '']);

        $response->assertStatus(422);
    }

    public function test_update_profile_rejects_an_email_already_used_by_another_account(): void
    {
        User::factory()->create(['email' => 'taken@example.ci']);
        $user = User::factory()->create(['email' => null]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user', [
            'name' => $user->name,
            'email' => 'taken@example.ci',
        ]);

        $response->assertStatus(422);
    }

    public function test_update_profile_allows_keeping_own_current_email(): void
    {
        $user = User::factory()->create(['email' => 'me@example.ci']);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/user', [
            'name' => 'New Name',
            'email' => 'me@example.ci',
        ]);

        $response->assertOk();
    }

    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->patchJson('/api/user', ['name' => 'New Name']);

        $response->assertStatus(401);
    }
}
