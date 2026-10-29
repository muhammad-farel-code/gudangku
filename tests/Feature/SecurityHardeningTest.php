<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Memastikan header keamanan standar diinjeksi pada setiap respons HTTP web.
     */
    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * Memastikan header HSTS dikirimkan ketika request dilakukan melalui HTTPS (secure).
     */
    public function test_hsts_header_is_present_on_secure_requests(): void
    {
        $response = $this->get('https://localhost/login');

        $response->assertStatus(200);
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    /**
     * Memastikan tag HTML berbahaya di-strip oleh sanitasi FormRequest
     * dan Blade merender karakter aman tanpa mengeksekusi tag mentah.
     */
    public function test_xss_payload_is_sanitized_and_escaped(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $xssInput = '<b>Kategori Elektronik</b>';

        $response = $this->actingAs($admin)->post('/categories', [
            'name' => $xssInput,
            'description' => '<img src=x onerror=alert(1)>Deskripsi aman',
        ]);

        $response->assertRedirect('/categories');

        // Pastikan tag <b> dan <img> dibersihkan oleh strip_tags
        $category = Category::where('name', 'Kategori Elektronik')->first();
        $this->assertNotNull($category, 'Tag HTML harus dibersihkan dari nama kategori.');
        $this->assertEquals('Kategori Elektronik', $category->name);
        $this->assertEquals('Deskripsi aman', $category->description);

        // Pastikan di view tidak merender tag HTML berbahaya secara mentah
        $viewResponse = $this->actingAs($admin)->get('/categories');
        $viewResponse->assertDontSee('<b>Kategori Elektronik</b>', false);
        $viewResponse->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    /**
     * Memastikan percobaan mass assignment untuk mengubah stok produk secara langsung via HTTP update ditolak/diabaikan.
     */
    public function test_stock_cannot_be_manipulated_via_product_update_mass_assignment(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Elektronik Uji',
            'description' => 'Kategori untuk uji keamanan',
        ]);

        $supplier = Supplier::create([
            'name' => 'PT Mitra Aman',
            'contact_name' => 'Budi',
            'phone' => '081234567890',
            'email' => 'mitra@aman.com',
            'address' => 'Jl. Keamanan No. 1',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PROD-SEC-001',
            'name' => 'Produk Asli',
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 10,
            'min_stock' => 5,
            'rack_location' => 'A-01',
            'is_active' => true,
        ]);

        // Coba injeksi parameter stock = 99999 pada permintaan update
        $response = $this->actingAs($admin)->put("/products/{$product->id}", [
            'category_id' => $category->id,
            'supplier_id' => $supplier->id,
            'sku' => 'PROD-SEC-001',
            'name' => 'Produk Diperbarui',
            'unit' => 'pcs',
            'purchase_price' => 12000,
            'selling_price' => 18000,
            'stock' => 99999, // Parameter injeksi yang harus diabaikan oleh validated()
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $response->assertRedirect('/products');

        $product->refresh();
        $this->assertEquals('Produk Diperbarui', $product->name);
        $this->assertEquals(10, $product->stock, 'Stok tidak boleh berubah melalui update produk!');
    }

    /**
     * Memastikan query filter dengan payload SQL injection dieksekusi secara aman menggunakan PDO binding.
     */
    public function test_sql_injection_payload_in_search_query_is_treated_as_literal_string(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'ATK Aman',
            'description' => 'Kategori ATK',
        ]);

        // Simpan produk normal
        Product::create([
            'category_id' => $category->id,
            'sku' => 'SAFE-001',
            'name' => 'Buku Catatan Aman',
            'unit' => 'buku',
            'purchase_price' => 5000,
            'selling_price' => 8000,
            'stock' => 50,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        // Kirim berbagai variasi payload SQL Injection
        $payloads = [
            "' OR '1'='1",
            "'; DROP TABLE products; --",
            "1' UNION SELECT null, null, null--",
        ];

        foreach ($payloads as $payload) {
            $response = $this->actingAs($staff)->get('/products?search=' . urlencode($payload));
            $response->assertStatus(200);
            // Tidak boleh terjadi crash database 500
        }

        // Pastikan tabel products masih utuh
        $this->assertDatabaseHas('products', [
            'sku' => 'SAFE-001',
        ]);
    }
}
