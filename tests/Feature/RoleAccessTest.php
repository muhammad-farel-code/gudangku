<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan tamu (guest) yang belum login diarahkan ke halaman login.
     */
    public function test_guest_is_redirected_to_login_on_protected_routes(): void
    {
        $protectedRoutes = [
            '/dashboard',
            '/products',
            '/categories',
            '/suppliers',
            '/stock/in',
            '/stock/out',
            '/stock/adjustment',
            '/reports/stock',
            '/reports/transactions',
            '/users',
            '/activity-logs',
            '/profile',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    /**
     * Memastikan staf (staff) ditolak dengan status 403 saat mengakses area terlarang:
     * - /users (khusus admin)
     * - /reports/stock & /reports/transactions (khusus manager & admin)
     * - /stock/adjustment (khusus manager & admin)
     * - POST /products (khusus manager & admin)
     * - /activity-logs (khusus admin)
     */
    public function test_staff_gets_forbidden_403_on_restricted_routes(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Elektronik Uji',
            'description' => 'Kategori untuk uji RBAC',
        ]);

        $supplier = Supplier::create([
            'name' => 'PT Mitra Staf',
            'contact_name' => 'Staf Contact',
            'phone' => '081234567890',
            'email' => 'mitra@staf.com',
            'address' => 'Jl. Staf No. 1',
            'is_active' => true,
        ]);

        // 1. /users -> 403
        $this->actingAs($staff)->get('/users')->assertStatus(403);

        // 2. /reports/stock & /reports/transactions -> 403
        $this->actingAs($staff)->get('/reports/stock')->assertStatus(403);
        $this->actingAs($staff)->get('/reports/transactions')->assertStatus(403);

        // 3. /stock/adjustment -> 403
        $this->actingAs($staff)->get('/stock/adjustment')->assertStatus(403);
        $this->actingAs($staff)->post('/stock/adjustment', [])->assertStatus(403);

        // 4. POST /products -> 403
        $this->actingAs($staff)->post('/products', [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PROD-STAFF-FORBIDDEN',
            'name' => 'Produk Ditolak',
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'min_stock' => 5,
        ])->assertStatus(403);

        // 5. /activity-logs -> 403
        $this->actingAs($staff)->get('/activity-logs')->assertStatus(403);
    }

    /**
     * Memastikan manajer (manager) ditolak dengan 403 saat mengakses manajemen pengguna (/users)
     * dan log aktivitas (/activity-logs), namun dapat mengakses laporan dan penyesuaian stok.
     */
    public function test_manager_gets_forbidden_403_on_user_management(): void
    {
        $manager = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        // Manager dilarang mengakses manajemen pengguna
        $this->actingAs($manager)->get('/users')->assertStatus(403);
        $this->actingAs($manager)->get('/users/create')->assertStatus(403);

        // Manager dilarang mengakses activity logs
        $this->actingAs($manager)->get('/activity-logs')->assertStatus(403);

        // Namun Manager dapat mengakses laporan dan stok penyesuaian
        $this->actingAs($manager)->get('/reports/stock')->assertStatus(200);
        $this->actingAs($manager)->get('/reports/transactions')->assertStatus(200);
        $this->actingAs($manager)->get('/stock/adjustment')->assertStatus(200);
    }

    /**
     * Memastikan admin dapat mengakses seluruh rute sistem dengan status 200.
     */
    public function test_admin_has_access_to_all_system_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $routes = [
            '/dashboard',
            '/products',
            '/products/create',
            '/categories',
            '/categories/create',
            '/suppliers',
            '/suppliers/create',
            '/stock/in',
            '/stock/out',
            '/stock/adjustment',
            '/reports/stock',
            '/reports/transactions',
            '/users',
            '/users/create',
            '/activity-logs',
            '/profile',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertStatus(200);
        }
    }
}
