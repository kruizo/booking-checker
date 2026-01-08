<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->otherUser = User::factory()->create();
    }

    // ==========================================
    // UPDATE PROFILE TESTS (Current User)
    // ==========================================

    public function test_user_can_update_own_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/user', [
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_update_email(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/user', [
                'email' => 'newemail@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.email', 'newemail@example.com');
    }

    public function test_user_cannot_update_to_existing_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/user', [
                'email' => 'taken@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ==========================================
    // ADMIN: LIST USERS TESTS
    // ==========================================

    public function test_admin_can_list_all_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data',
                'pagination',
            ]);

        // Should include all 3 users (user, admin, otherUser)
        $this->assertEquals(3, $response->json('pagination.total'));
    }

    public function test_regular_user_cannot_list_users(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_search_users_by_keyword(): void
    {
        User::factory()->create(['name' => 'Searchable User']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?keyword=Searchable');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_admin_can_search_users_by_email(): void
    {
        User::factory()->create(['email' => 'findme@test.com']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?keyword=findme@test.com');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    public function test_users_list_supports_pagination(): void
    {
        User::factory()->count(20)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?per_page=10&page=1');

        $response->assertStatus(200);
        $this->assertEquals(10, count($response->json('data')));
        $this->assertEquals(23, $response->json('pagination.total')); // 20 + 3 from setUp
    }

    public function test_users_list_supports_sorting(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users?sort_by=name&sort_direction=asc');

        $response->assertStatus(200);
        
        $names = array_column($response->json('data'), 'name');
        $sortedNames = $names;
        sort($sortedNames);
        
        $this->assertEquals($sortedNames, $names);
    }

    // ==========================================
    // ADMIN: SHOW USER TESTS
    // ==========================================

    public function test_admin_can_view_any_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/admin/users/{$this->user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.user.id', $this->user->id);
    }

    public function test_regular_user_cannot_view_other_users(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/admin/users/{$this->otherUser->id}");

        $response->assertStatus(403);
    }

    public function test_viewing_nonexistent_user_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/admin/users/99999');

        $response->assertStatus(404);
    }

    // ==========================================
    // ADMIN: UPDATE USER TESTS
    // ==========================================

    public function test_admin_can_update_any_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/admin/users/{$this->user->id}", [
                'name' => 'Admin Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Admin Updated Name',
        ]);
    }

    public function test_regular_user_cannot_update_other_users(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/admin/users/{$this->otherUser->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(403);
    }

    // ==========================================
    // ADMIN: TOGGLE PERMISSION TESTS
    // ==========================================

    public function test_admin_can_toggle_user_permission(): void
    {
        $this->assertFalse($this->user->is_admin);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/admin/users/{$this->user->id}/permission");

        $response->assertStatus(200);
        $this->assertTrue($this->user->fresh()->is_admin);

        // Toggle back
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/admin/users/{$this->user->id}/permission");

        $response->assertStatus(200);
        $this->assertFalse($this->user->fresh()->is_admin);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $response = $this->actingAs($this->admin)
            ->patchJson("/api/v1/admin/users/{$this->admin->id}/permission");

        $response->assertStatus(403);
        $this->assertTrue($this->admin->fresh()->is_admin);
    }

    public function test_regular_user_cannot_toggle_permissions(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/admin/users/{$this->otherUser->id}/permission");

        $response->assertStatus(403);
    }

    // ==========================================
    // SELF TOGGLE PERMISSION (Testing Route)
    // ==========================================

    public function test_user_can_toggle_own_permission_via_testing_route(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson("/api/v1/user/{$this->user->id}/permission");

        $response->assertStatus(200);
        $this->assertTrue($this->user->fresh()->is_admin);
    }

    public function test_user_cannot_demote_themselves_via_testing_route(): void
    {
        $adminUser = User::factory()->admin()->create();

        $response = $this->actingAs($adminUser)
            ->patchJson("/api/v1/user/{$adminUser->id}/permission");

        // Should fail with self-demotion protection
        $response->assertStatus(403);
        $this->assertTrue($adminUser->fresh()->is_admin);
    }
}
