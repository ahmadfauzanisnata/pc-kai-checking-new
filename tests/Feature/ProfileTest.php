<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Admin Role',
        ]);

        $this->user = User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.show'));
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    public function test_can_view_change_password_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.password'));
        $response->assertStatus(200);
        $response->assertSee('Ubah Kata Sandi');
    }

    public function test_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password' => 'password123',
            'password' => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('new-secret-password-123', $this->user->fresh()->password));
    }

    public function test_cannot_update_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'new-secret-password-123',
            'password_confirmation' => 'new-secret-password-123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }
}
