<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;
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

        $category = Category::create([
            'name' => 'Elektronik',
            'description' => 'Barang elektronik',
        ]);

        $supplier = Supplier::create([
            'name' => 'PT Makmur Jaya',
            'email' => 'makmur@example.com',
            'phone' => '081234567890',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'ELK-001',
            'name' => 'Mouse Wireless',
            'unit' => 'pcs',
            'purchase_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'min_stock' => 15, // Stok menipis
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_admin_and_manager_can_view_dashboard_with_inventory_value(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('GudangKu');
        $response->assertSee('Total Produk Aktif');
        $response->assertSee('Nilai Persediaan');
        $response->assertViewHas('inventoryValue');

        // Pastikan manager juga dapat melihat
        $responseMgr = $this->actingAs($this->manager)->get('/dashboard');
        $responseMgr->assertOk();
        $responseMgr->assertSee('Nilai Persediaan');
        $responseMgr->assertViewHas('inventoryValue');
    }

    public function test_staff_does_not_see_or_receive_inventory_value_on_dashboard(): void
    {
        $response = $this->actingAs($this->staff)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('GudangKu');
        $response->assertSee('Total Produk Aktif');
        $response->assertDontSee('Nilai Persediaan');
        $response->assertSee('Transaksi Hari Ini');

        // Server-side guard: variabel inventoryValue TIDAK BOLEH dikirim ke view staff
        $response->assertViewMissing('inventoryValue');
        $response->assertViewHas('todayTransactionsCount');
    }

    public function test_dashboard_chart_data_contains_exactly_14_days(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('chartData');

        $chartData = $response->viewData('chartData');
        $this->assertCount(14, $chartData['labels']);
        $this->assertCount(14, $chartData['stockIn']);
        $this->assertCount(14, $chartData['stockOut']);
    }

    public function test_dashboard_provides_restock_recommendations_and_out_of_stock_products(): void
    {
        // Tambahkan produk stok habis
        $outOfStockProduct = Product::create([
            'category_id' => $this->product->category_id,
            'sku' => 'ELK-HABIS',
            'name' => 'Kabel HDMI Rusak/Habis',
            'unit' => 'unit',
            'purchase_price' => 20000,
            'selling_price' => 35000,
            'stock' => 0,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard');

        $response->assertOk();
        $response->assertViewHas('restockRecommendations');
        $response->assertViewHas('outOfStockProducts');

        $recommendations = $response->viewData('restockRecommendations');
        $this->assertNotEmpty($recommendations);

        // Periksa perhitungan saran restock (min_stock * 2 - stock)
        // Untuk $this->product: min_stock 15, stock 10 -> saran: 15*2 - 10 = 20
        $mouseRec = $recommendations->firstWhere('id', $this->product->id);
        $this->assertNotNull($mouseRec);
        $this->assertEquals(20, $mouseRec->suggested_order);

        // Periksa produk habis
        $outOfStock = $response->viewData('outOfStockProducts');
        $this->assertTrue($outOfStock->contains('id', $outOfStockProduct->id));
    }
}
