<?php

namespace Tests\Feature;

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

        $this->user = User::factory()->create([
            'name' => 'Nama Asli',
            'email' => 'user@gudangku.test',
            'password' => Hash::make('OldPassword123!'),
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertOk();
        $response->assertSee('Nama Asli');
        $response->assertSee('user@gudangku.test');
    }

    public function test_user_can_update_profile_name(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.update'), [
            'name' => 'Nama Baru Yang Keren',
        ]);

        $response->assertRedirect();
        $this->user->refresh();
        $this->assertEquals('Nama Baru Yang Keren', $this->user->name);
    }

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertRedirect();
        $this->user->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword123!', $this->user->password));
    }

    public function test_password_update_fails_if_current_password_is_wrong(): void
    {
        $response = $this->actingAs($this->user)->put(route('profile.password.update'), [
            'current_password' => 'SalahPassword!',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->user->refresh();
        $this->assertTrue(Hash::check('OldPassword123!', $this->user->password));
    }
}
