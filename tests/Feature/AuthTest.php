<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('GudangKu');
        $response->assertSee('Alamat Email');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'staff.test@gudangku.test',
            'password' => Hash::make('Staff@12345'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'staff.test@gudangku.test',
            'password' => 'Staff@12345',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'login',
            'user_id' => $user->id,
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_password_and_get_generic_error(): void
    {
        User::factory()->create([
            'email' => 'staff.test@gudangku.test',
            'password' => Hash::make('Staff@12345'),
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'staff.test@gudangku.test',
            'password' => 'WrongPassword999',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email' => 'Email atau kata sandi yang Anda masukkan salah.']);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'nonaktif@gudangku.test',
            'password' => Hash::make('Staff@12345'),
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'nonaktif@gudangku.test',
            'password' => 'Staff@12345',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_register_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Akun Baru');
    }

    public function test_new_user_role_is_always_staff_even_if_role_admin_injected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Attacker Escalation',
            'email' => 'hacker@gudangku.test',
            'password' => 'Hacker@12345',
            'password_confirmation' => 'Hacker@12345',
            'role' => 'admin', // Percobaan manipulasi input role
            'is_active' => false,
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'hacker@gudangku.test')->first();
        $this->assertNotNull($user);
        // Pastikan role tetap 'staff', BUKAN 'admin'
        $this->assertEquals('staff', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'register',
            'user_id' => $user->id,
        ]);
    }

    public function test_users_can_logout_via_post_request(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Staff@12345'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'logout',
            'user_id' => $user->id,
        ]);
    }

    public function test_ensure_user_is_active_middleware_logs_out_deactivated_user(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Staff@12345'),
            'is_active' => false, // Dinonaktifkan
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_login_rate_limiting_throttles_after_five_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'victim@gudangku.test',
                'password' => 'WrongPassword',
            ]);
        }

        // Percobaan ke-6 harus di-throttle (HTTP 429)
        $response = $this->post('/login', [
            'email' => 'victim@gudangku.test',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(429);
    }
}
