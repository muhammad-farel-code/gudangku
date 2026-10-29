<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_dashboard_with_inventory_value(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('GudangKu');
        $response->assertSee('Total Produk');
        $response->assertSee('Nilai Persediaan');
    }

    public function test_staff_does_not_see_inventory_value_on_dashboard(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('GudangKu');
        $response->assertSee('Total Produk');
        $response->assertDontSee('Nilai Persediaan');
        $response->assertSee('Aktivitas Transaksi');
    }
}
