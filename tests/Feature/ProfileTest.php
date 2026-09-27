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

    public function test_can_save_digital_signature_from_canvas(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $base64Png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->actingAs($this->user)->postJson(route('profile.signature'), [
            'signature_data' => $base64Png,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user = $this->user->fresh();
        $this->assertNotNull($user->signature_path);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($user->signature_path));
    }

    public function test_can_delete_digital_signature(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/test.png', 'dummy');
        $this->user->update(['signature_path' => 'signatures/test.png']);

        $response = $this->actingAs($this->user)->postJson(route('profile.signature'), [
            'signature_data' => 'clear',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user = $this->user->fresh();
        $this->assertNull($user->signature_path);
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists('signatures/test.png'));
    }
}
