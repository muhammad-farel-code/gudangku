<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private Category $category;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
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

        $this->category = Category::create([
            'name' => 'Elektronik',
            'description' => 'Barang elektronik',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'PT Mitra Sentosa',
            'email' => 'mitra@example.com',
            'phone' => '081234567890',
        ]);
    }

    public function test_guest_cannot_access_products(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_product_list_and_detail_but_not_purchase_price(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'PRD-001',
            'name' => 'Keyboard Mechanical',
            'unit' => 'pcs',
            'purchase_price' => 250000,
            'selling_price' => 450000,
            'stock' => 15,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->staff)->get(route('products.index'));
        $response->assertOk();
        $response->assertSee('Keyboard Mechanical');
        $response->assertSee('450.000'); // Selling price
        $response->assertDontSee('250.000'); // Purchase price hidden for staff

        $detailResponse = $this->actingAs($this->staff)->get(route('products.show', $product));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Keyboard Mechanical');
        $detailResponse->assertSee('450.000');
        $detailResponse->assertDontSee('250.000');
    }

    public function test_staff_cannot_create_or_modify_or_delete_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PRD-002',
            'name' => 'Mouse Gaming',
            'unit' => 'pcs',
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'min_stock' => 3,
            'is_active' => true,
        ]);

        $this->actingAs($this->staff)->get(route('products.create'))->assertForbidden();

        $this->actingAs($this->staff)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'sku' => 'PRD-003',
            'name' => 'Headset Pro',
            'unit' => 'pcs',
            'purchase_price' => 100000,
            'selling_price' => 180000,
            'min_stock' => 5,
        ])->assertForbidden();

        $this->actingAs($this->staff)->get(route('products.edit', $product))->assertForbidden();

        $this->actingAs($this->staff)->put(route('products.update', $product), [
            'category_id' => $this->category->id,
            'sku' => 'PRD-002',
            'name' => 'Mouse Gaming Updated',
            'unit' => 'pcs',
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'min_stock' => 3,
        ])->assertForbidden();

        $this->actingAs($this->staff)->delete(route('products.destroy', $product))->assertForbidden();
    }

    public function test_manager_can_create_product_with_initial_stock(): void
    {
        $response = $this->actingAs($this->manager)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'prd-010', // will be upper-cased
            'name' => 'Monitor 24 Inch',
            'description' => 'Panel IPS Full HD',
            'unit' => 'unit',
            'purchase_price' => 1200000,
            'selling_price' => 1650000,
            'initial_stock' => 20,
            'min_stock' => 5,
            'rack_location' => 'rak-b1',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'sku' => 'PRD-010',
            'name' => 'Monitor 24 Inch',
            'stock' => 20,
            'unit' => 'unit',
            'rack_location' => 'RAK-B1',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->manager->id,
            'action' => 'create',
        ]);
    }

    public function test_validation_fails_if_selling_price_less_than_purchase_price_br10(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'sku' => 'PRD-FAIL',
            'name' => 'Produk Rugi',
            'unit' => 'pcs',
            'purchase_price' => 100000,
            'selling_price' => 80000, // < purchase_price
            'min_stock' => 5,
        ]);

        $response->assertSessionHasErrors(['selling_price']);
    }

    public function test_validation_fails_if_sku_is_duplicate(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-EXIST',
            'name' => 'Produk Awal',
            'unit' => 'pcs',
            'purchase_price' => 50000,
            'selling_price' => 75000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'sku' => 'SKU-EXIST',
            'name' => 'Produk Duplikat',
            'unit' => 'pcs',
            'purchase_price' => 50000,
            'selling_price' => 75000,
            'min_stock' => 2,
        ]);

        $response->assertSessionHasErrors(['sku']);
    }

    public function test_updating_product_does_not_modify_stock_br2(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PRD-SAFE',
            'name' => 'Nama Awal',
            'unit' => 'pcs',
            'purchase_price' => 50000,
            'selling_price' => 80000,
            'stock' => 45, // original stock
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('products.update', $product), [
            'category_id' => $this->category->id,
            'sku' => 'PRD-SAFE',
            'name' => 'Nama Diperbarui',
            'unit' => 'pcs',
            'purchase_price' => 55000,
            'selling_price' => 90000,
            'stock' => 9999, // Should NOT be accepted or applied
            'min_stock' => 12,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('products.index'));

        $product->refresh();
        $this->assertEquals('Nama Diperbarui', $product->name);
        $this->assertEquals(45, $product->stock); // Still 45 (BR2 enforced)
        $this->assertEquals(12, $product->min_stock);
    }

    public function test_cannot_delete_product_with_existing_stock_transactions_br6(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PRD-TX',
            'name' => 'Produk Bertransaksi',
            'unit' => 'pcs',
            'purchase_price' => 50000,
            'selling_price' => 75000,
            'stock' => 10,
            'min_stock' => 2,
        ]);

        // Simulasikan transaksi stok
        StockTransaction::create([
            'product_id' => $product->id,
            'user_id' => $this->admin->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_before' => 0,
            'stock_after' => 10,
            'reference_no' => 'IN-TEST-001',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_can_delete_product_without_transactions(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PRD-NOTX',
            'name' => 'Produk Tanpa Riwayat',
            'unit' => 'pcs',
            'purchase_price' => 50000,
            'selling_price' => 75000,
            'stock' => 0,
            'min_stock' => 2,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('products.destroy', $product));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delete',
        ]);
    }

    public function test_cannot_delete_category_that_contains_products_br5(): void
    {
        Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PRD-IN-CAT',
            'name' => 'Produk Dalam Kategori',
            'unit' => 'pcs',
            'purchase_price' => 20000,
            'selling_price' => 30000,
            'stock' => 5,
            'min_stock' => 1,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $this->category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }
}
