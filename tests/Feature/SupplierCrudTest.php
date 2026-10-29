<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->manager = User::factory()->create(['role' => 'manager', 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
    }

    public function test_all_authenticated_roles_can_view_supplier_index_and_show(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Mitra Sejahtera',
            'contact_person' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'budi@mitra.test',
        ]);

        $responseIndex = $this->actingAs($this->staff)->get(route('suppliers.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('PT Mitra Sejahtera');

        $responseShow = $this->actingAs($this->staff)->get(route('suppliers.show', $supplier));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Budi Santoso');
    }

    public function test_staff_cannot_create_or_store_supplier_returns_403(): void
    {
        $responseCreate = $this->actingAs($this->staff)->get(route('suppliers.create'));
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->staff)->post(route('suppliers.store'), [
            'name' => 'PT Ilegal',
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_admin_and_manager_can_store_supplier(): void
    {
        $response = $this->actingAs($this->manager)->post(route('suppliers.store'), [
            'name' => 'PT Logistik Global Indo',
            'contact_person' => 'Rian Hidayat',
            'phone' => '085712345678',
            'email' => 'rian@logistikglobal.co.id',
            'address' => 'Jl. Pelabuhan No. 10 Jakarta',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'PT Logistik Global Indo']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'create_supplier']);
    }

    public function test_store_supplier_validates_required_name_and_phone_regex(): void
    {
        // Uji nama wajib diisi
        $response = $this->actingAs($this->admin)->post(route('suppliers.store'), [
            'name' => '',
            'phone' => '123', // regex min 6 karakter
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);

        // Uji sanitasi strip_tags
        $this->actingAs($this->admin)->post(route('suppliers.store'), [
            'name' => '<b>PT Vendor Aman</b>',
            'contact_person' => '<script>alert(1)</script>Joko',
            'phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('suppliers', [
            'name' => 'PT Vendor Aman',
            'contact_person' => 'alert(1)Joko',
        ]);
    }

    public function test_admin_can_update_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'CV Sumber Rejeki',
            'contact_person' => 'Siti',
        ]);

        $response = $this->actingAs($this->admin)->put(route('suppliers.update', $supplier), [
            'name' => 'CV Sumber Makmur Rejeki',
            'contact_person' => 'Siti Rahma',
            'phone' => '081199887766',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'CV Sumber Makmur Rejeki']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'update_supplier']);
    }

    public function test_delete_supplier_sets_null_on_associated_products_br7(): void
    {
        $category = Category::create(['name' => 'Elektronik']);
        $supplier = Supplier::create(['name' => 'PT Pabrik Komponen']);

        $product = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'SUP-TEST-01',
            'name' => 'Produk dari Pabrik',
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 5,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $this->assertEquals($supplier->id, $product->supplier_id);

        // Hapus pemasok
        $response = $this->actingAs($this->admin)->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);

        // Verifikasi BR7: produk tetap ada, supplier_id menjadi null
        $product->refresh();
        $this->assertNull($product->supplier_id);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'delete_supplier']);
    }
}
