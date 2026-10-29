<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;
    private User $staff;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Elektronik']);
        $supplier = Supplier::create(['name' => 'PT Suplai']);

        $this->product = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => '=MALICIOUS-SKU', // Untuk uji formula injection
            'name' => '+Rumus Berbahaya',
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 50,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        StockTransaction::create([
            'reference_no' => 'IN-20261029-0001',
            'product_id' => $this->product->id,
            'user_id' => $this->manager->id,
            'type' => 'in',
            'quantity' => 50,
            'stock_before' => 0,
            'stock_after' => 50,
            'transaction_date' => now()->toDateString(),
            'notes' => '=SUM(A1:A10)', // Formula injection note
        ]);
    }

    public function test_staff_cannot_access_reports(): void
    {
        $this->actingAs($this->staff)->get(route('reports.stock'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('reports.transactions'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('reports.stock.export'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('reports.transactions.export'))->assertForbidden();
    }

    public function test_manager_can_view_stock_and_transaction_reports(): void
    {
        $responseStock = $this->actingAs($this->manager)->get(route('reports.stock'));
        $responseStock->assertOk();
        $responseStock->assertSee('Laporan Stok Inventaris');

        $responseTx = $this->actingAs($this->manager)->get(route('reports.transactions'));
        $responseTx->assertOk();
        $responseTx->assertSee('Laporan Mutasi Transaksi');
    }

    public function test_export_stock_csv_escapes_formula_injection(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.stock.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // Capture streamed content
        $content = $response->streamedContent();

        // SKU '=MALICIOUS-SKU' harus diawali petik tunggal "'=MALICIOUS-SKU"
        $this->assertStringContainsString("'=MALICIOUS-SKU", $content);
        // Nama '+Rumus Berbahaya' harus diawali petik tunggal "'+Rumus Berbahaya"
        $this->assertStringContainsString("'+Rumus Berbahaya", $content);
    }

    public function test_export_transactions_csv_escapes_formula_injection(): void
    {
        $response = $this->actingAs($this->manager)->get(route('reports.transactions.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString("IN-20261029-0001", $content);
        $this->assertStringContainsString("'=SUM(A1:A10)", $content);
    }
}
