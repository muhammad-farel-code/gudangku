<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class StockTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
    private Category $category;
    private Supplier $supplier;
    private Product $product;

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
            'name' => 'Alat Tulis',
            'description' => 'Perlengkapan ATK',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'CV Sumber Kertas',
            'email' => 'kertas@example.com',
            'phone' => '081234567890',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'KRT-A4-70',
            'name' => 'Kertas HVS A4 70gr',
            'unit' => 'rim',
            'purchase_price' => 45000,
            'selling_price' => 55000,
            'stock' => 50,
            'min_stock' => 10,
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_stock_routes(): void
    {
        $this->get(route('stock.in'))->assertRedirect(route('login'));
        $this->get(route('stock.out'))->assertRedirect(route('login'));
        $this->get(route('stock.adjustment'))->assertRedirect(route('login'));
        $this->get(route('transactions.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_view_forms_and_perform_stock_in_and_out(): void
    {
        $this->actingAs($this->staff)->get(route('stock.in'))->assertOk();
        $this->actingAs($this->staff)->get(route('stock.out'))->assertOk();

        // Submit Stok Masuk
        $responseIn = $this->actingAs($this->staff)->post(route('stock.in.store'), [
            'product_id' => $this->product->id,
            'quantity' => 20,
            'supplier_id' => $this->supplier->id,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Tambahan persediaan rim',
        ]);

        $responseIn->assertRedirect();
        $this->product->refresh();
        $this->assertEquals(70, $this->product->stock);

        // Submit Stok Keluar
        $responseOut = $this->actingAs($this->staff)->post(route('stock.out.store'), [
            'product_id' => $this->product->id,
            'quantity' => 15,
            'recipient' => 'Bagian Keuangan',
            'transaction_date' => now()->toDateString(),
            'notes' => 'Kebutuhan cetak laporan pajak',
        ]);

        $responseOut->assertRedirect();
        $this->product->refresh();
        $this->assertEquals(55, $this->product->stock);
    }

    public function test_staff_cannot_access_or_submit_stock_adjustment(): void
    {
        $this->actingAs($this->staff)->get(route('stock.adjustment'))->assertForbidden();

        $this->actingAs($this->staff)->post(route('stock.adjustment.store'), [
            'product_id' => $this->product->id,
            'actual_stock' => 40,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Penyesuaian oleh staff tidak diizinkan',
        ])->assertForbidden();
    }

    public function test_manager_and_admin_can_perform_stock_adjustment(): void
    {
        $response = $this->actingAs($this->manager)->post(route('stock.adjustment.store'), [
            'product_id' => $this->product->id,
            'actual_stock' => 48,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Hasil opname: 2 rim rusak terkena tumpahan tinta',
        ]);

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals(48, $this->product->stock);

        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $this->product->id,
            'type' => 'adjustment',
            'quantity' => 2,
            'stock_before' => 50,
            'stock_after' => 48,
        ]);
    }

    public function test_stock_out_returns_error_when_quantity_exceeds_available_stock(): void
    {
        $response = $this->actingAs($this->staff)->post(route('stock.out.store'), [
            'product_id' => $this->product->id,
            'quantity' => 999, // > 50
            'recipient' => 'Cabang Bali',
            'transaction_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->product->refresh();
        $this->assertEquals(50, $this->product->stock); // Tidak berkurang
    }

    public function test_validation_fails_for_adjustment_notes_less_than_5_characters(): void
    {
        $response = $this->actingAs($this->admin)->post(route('stock.adjustment.store'), [
            'product_id' => $this->product->id,
            'actual_stock' => 45,
            'transaction_date' => now()->toDateString(),
            'notes' => 'tes', // < 5 chars
        ]);

        $response->assertSessionHasErrors(['notes']);
    }

    public function test_stock_transactions_are_immutable_policy_denies_edit_and_delete(): void
    {
        $stockService = app(StockService::class);
        $tx = $stockService->stockIn($this->product, 10, $this->admin, $this->supplier->id);

        // Policy harus melarang update dan delete untuk SEMUA role
        $this->assertFalse(Gate::forUser($this->admin)->allows('update', $tx));
        $this->assertFalse(Gate::forUser($this->admin)->allows('delete', $tx));
        $this->assertFalse(Gate::forUser($this->manager)->allows('update', $tx));
        $this->assertFalse(Gate::forUser($this->manager)->allows('delete', $tx));
        $this->assertFalse(Gate::forUser($this->staff)->allows('update', $tx));
        $this->assertFalse(Gate::forUser($this->staff)->allows('delete', $tx));
    }

    public function test_transaction_index_can_be_filtered(): void
    {
        $stockService = app(StockService::class);

        $tx1 = $stockService->stockIn($this->product, 10, $this->staff, $this->supplier->id, 'Masuk 1', '2026-10-20');
        $tx2 = $stockService->stockOut($this->product, 5, $this->staff, 'Penerima A', 'Keluar 1', '2026-10-25');

        // Filter tipe 'out'
        $response = $this->actingAs($this->staff)->get(route('transactions.index', ['type' => 'out']));
        $response->assertOk();
        $response->assertSee($tx2->reference_no);
        $response->assertDontSee($tx1->reference_no);

        // Filter produk_id
        $responseProd = $this->actingAs($this->staff)->get(route('transactions.index', ['product_id' => $this->product->id]));
        $responseProd->assertOk();
        $responseProd->assertSee($tx1->reference_no);
        $responseProd->assertSee($tx2->reference_no);
    }
}
