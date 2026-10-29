<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Category $category;
    private Supplier $supplier;
    private Product $product;
    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Elektronik',
            'description' => 'Kategori elektronik',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'PT Makmur Jaya',
            'email' => 'makmur@example.com',
            'phone' => '081234567890',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'PRD-TEST-01',
            'name' => 'Kabel LAN Cat6',
            'unit' => 'meter',
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'stock' => 100,
            'min_stock' => 20,
            'is_active' => true,
        ]);

        $this->stockService = app(StockService::class);
    }

    public function test_generate_reference_produces_unique_sequential_format_br12(): void
    {
        $date = '2026-10-29';
        $ref1 = $this->stockService->generateReference('in', $date);
        $this->assertEquals('IN-20261029-0001', $ref1);

        // Simulasikan transaksi tersimpan
        StockTransaction::create([
            'reference_no' => $ref1,
            'product_id' => $this->product->id,
            'user_id' => $this->staff->id,
            'type' => 'in',
            'quantity' => 10,
            'stock_before' => 100,
            'stock_after' => 110,
            'transaction_date' => $date,
        ]);

        $ref2 = $this->stockService->generateReference('in', $date);
        $this->assertEquals('IN-20261029-0002', $ref2);

        $refOut = $this->stockService->generateReference('out', $date);
        $this->assertEquals('OUT-20261029-0001', $refOut);

        $refAdj = $this->stockService->generateReference('adjustment', $date);
        $this->assertEquals('ADJ-20261029-0001', $refAdj);
    }

    public function test_stock_in_increases_product_stock_and_creates_transaction(): void
    {
        $initialStock = $this->product->stock; // 100
        $qty = 25;

        $tx = $this->stockService->stockIn(
            product: $this->product,
            quantity: $qty,
            user: $this->staff,
            supplierId: $this->supplier->id,
            notes: 'Penerimaan barang dari PO-123',
            date: '2026-10-29'
        );

        $this->assertInstanceOf(StockTransaction::class, $tx);
        $this->assertEquals('in', $tx->type);
        $this->assertEquals($initialStock, $tx->stock_before);
        $this->assertEquals($initialStock + $qty, $tx->stock_after);
        $this->assertEquals($qty, $tx->quantity);
        $this->assertStringStartsWith('IN-20261029-', $tx->reference_no);

        $this->product->refresh();
        $this->assertEquals($initialStock + $qty, $this->product->stock);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'stock_in',
            'user_id' => $this->staff->id,
        ]);
    }

    public function test_stock_out_decreases_product_stock_and_creates_transaction(): void
    {
        $initialStock = $this->product->stock; // 100
        $qty = 30;

        $tx = $this->stockService->stockOut(
            product: $this->product,
            quantity: $qty,
            user: $this->staff,
            recipient: 'Divisi Server',
            notes: 'Pemasangan kabel ruang server',
            date: '2026-10-29'
        );

        $this->assertInstanceOf(StockTransaction::class, $tx);
        $this->assertEquals('out', $tx->type);
        $this->assertEquals('Divisi Server', $tx->recipient);
        $this->assertEquals($initialStock, $tx->stock_before);
        $this->assertEquals($initialStock - $qty, $tx->stock_after);
        $this->assertEquals($qty, $tx->quantity);

        $this->product->refresh();
        $this->assertEquals($initialStock - $qty, $this->product->stock);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'stock_out',
            'user_id' => $this->staff->id,
        ]);
    }

    public function test_stock_out_throws_insufficient_stock_exception_when_requested_exceeds_stock_br1(): void
    {
        $this->expectException(InsufficientStockException::class);

        // Minta 200, stok hanya 100
        $this->stockService->stockOut(
            product: $this->product,
            quantity: 200,
            user: $this->staff,
            recipient: 'Klien Luar'
        );

        // Stok produk tidak boleh berubah
        $this->product->refresh();
        $this->assertEquals(100, $this->product->stock);
    }

    public function test_stock_adjustment_updates_stock_and_records_difference(): void
    {
        $initialStock = $this->product->stock; // 100
        $actualStock = 92; // Selisih 8

        $tx = $this->stockService->adjust(
            product: $this->product,
            actualStock: $actualStock,
            user: $this->admin,
            notes: 'Stock opname akhir pekan: 8 meter rusak digigit tikus',
            date: '2026-10-29'
        );

        $this->assertInstanceOf(StockTransaction::class, $tx);
        $this->assertEquals('adjustment', $tx->type);
        $this->assertEquals($initialStock, $tx->stock_before);
        $this->assertEquals($actualStock, $tx->stock_after);
        $this->assertEquals(8, $tx->quantity);

        $this->product->refresh();
        $this->assertEquals(92, $this->product->stock);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'stock_adjustment',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_product_creation_with_initial_stock_automatically_triggers_stock_in(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'SKU-INIT-01',
            'name' => 'Switch Hub 16 Port',
            'unit' => 'unit',
            'purchase_price' => 500000,
            'selling_price' => 750000,
            'initial_stock' => 15,
            'min_stock' => 3,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('sku', 'SKU-INIT-01')->firstOrFail();
        $this->assertEquals(15, $product->stock);

        // Pastikan transaksi Stok Awal tercatat di stock_transactions
        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 15,
            'stock_before' => 0,
            'stock_after' => 15,
            'notes' => 'Stok awal',
        ]);
    }
}
