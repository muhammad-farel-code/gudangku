<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@gudangku.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_staff_and_manager_cannot_access_user_management(): void
    {
        $this->actingAs($this->staff)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->manager)->get(route('users.index'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('users.create'))->assertForbidden();
    }

    public function test_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Staf Baru',
            'email' => 'stafbaru@gudangku.test',
            'role' => 'staff',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'stafbaru@gudangku.test',
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_delete_self_br9(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->admin));

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_change_own_role_or_deactivate_self_br9(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->admin), [
            'name' => 'Admin Utama Updated',
            'email' => 'admin@gudangku.test',
            'role' => 'staff', // Mencoba ubah role sendiri
            'is_active' => 0,  // Mencoba nonaktifkan sendiri
        ]);

        $this->admin->refresh();
        $this->assertEquals('admin', $this->admin->role);
        $this->assertTrue($this->admin->is_active);
    }

    public function test_cannot_deactivate_or_demote_last_active_admin_br9(): void
    {
        // Hanya ada 1 admin di DB ($this->admin). Coba ubah target admin tunggal via PUT
        // Buat admin kedua terlebih dahulu untuk menguji skenario
        $secondAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Hapus admin kedua
        $this->actingAs($this->admin)->delete(route('users.destroy', $secondAdmin));
        $this->assertDatabaseMissing('users', ['id' => $secondAdmin->id]);

        // Sekarang hanya tersisa 1 admin ($this->admin). Buat admin palsu untuk coba di-demote
        $targetAdmin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        // Nonaktifkan admin pertama kita
        $this->admin->is_active = false;
        $this->admin->save();

        // Sekarang targetAdmin adalah satu-satunya admin aktif. Admin nonaktif coba ubah role targetAdmin
        $response = $this->actingAs($targetAdmin)->put(route('users.update', $targetAdmin), [
            'name' => 'Target Admin',
            'email' => $targetAdmin->email,
            'role' => 'staff',
            'is_active' => 0,
        ]);

        $targetAdmin->refresh();
        $this->assertEquals('admin', $targetAdmin->role);
        $this->assertTrue($targetAdmin->is_active);
    }

    public function test_user_with_stock_transactions_cannot_be_deleted(): void
    {
        $category = Category::create(['name' => 'Elek']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'PRD-DEL',
            'name' => 'Tes',
            'unit' => 'pcs',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 5,
            'min_stock' => 1,
        ]);

        StockTransaction::create([
            'reference_no' => 'IN-20261029-9999',
            'product_id' => $product->id,
            'user_id' => $this->staff->id,
            'type' => 'in',
            'quantity' => 5,
            'stock_before' => 0,
            'stock_after' => 5,
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('users.destroy', $this->staff));

        $this->assertDatabaseHas('users', ['id' => $this->staff->id]);
    }
}
