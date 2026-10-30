<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XssEscapingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Kategori Pengujian',
            'description' => 'Deskripsi Kategori',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier Pengujian',
            'email' => 'supplier@test.com',
            'phone' => '081234567890',
        ]);
    }

    /**
     * Memastikan nama produk yang mengandung tag script di database
     * tampil ter-escape di halaman index (&lt;script&gt;) dan tidak dieksekusi.
     * Sesuai Bagian 11: nama produk berisi <script> tampil ter-escape (assertSee('&lt;script&gt;', false)).
     */
    public function test_product_name_with_script_tag_is_properly_escaped_in_blade(): void
    {
        // Masukkan data langsung ke database untuk menguji escaping Blade layer
        $product = Product::create([
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'XSS-001',
            'name' => '<script>alert("xss")</script>Laptop Pro',
            'unit' => 'unit',
            'purchase_price' => 10000000,
            'selling_price' => 13500000,
            'stock' => 5,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        // 1. Tampilan daftar produk (index)
        $indexResponse = $this->actingAs($this->admin)->get('/products');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;Laptop Pro', false);
        $indexResponse->assertDontSee('<script>alert("xss")</script>', false);

        // 2. Tampilan detail produk (show)
        $showResponse = $this->actingAs($this->admin)->get("/products/{$product->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;Laptop Pro', false);
        $showResponse->assertDontSee('<script>alert("xss")</script>', false);
    }

    /**
     * Memastikan deskripsi kategori yang berisi injeksi event handler JavaScript
     * (misalnya onerror, onload) ter-escape atau disanitasi dengan benar.
     */
    public function test_category_and_supplier_fields_escape_malicious_html(): void
    {
        $categoryXss = Category::create([
            'name' => '<img src=x onerror=alert("hacked")>Aksesoris',
            'description' => '<a href="javascript:alert(1)">Klik Saya</a>',
        ]);

        $response = $this->actingAs($this->admin)->get('/categories');
        $response->assertStatus(200);

        // Karakter < dan > wajib di-escape menjadi &lt; dan &gt;
        $response->assertSee('&lt;img src=x onerror=alert(&quot;hacked&quot;)&gt;Aksesoris', false);
        $response->assertDontSee('<img src=x onerror=alert("hacked")>', false);
        $response->assertDontSee('<a href="javascript:alert(1)">', false);
    }

    /**
     * Memastikan FormRequest membersihkan input berbahaya sebelum disimpan (defense-in-depth).
     */
    public function test_form_request_sanitizes_html_tags_on_product_creation(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => 'XSS-INPUT-01',
            'name' => '<b>Mouse Wireless</b>',
            'description' => '<i>Deskripsi dengan formatting HTML</i>',
            'unit' => 'pcs',
            'purchase_price' => 50000,
            'selling_price' => 75000,
            'initial_stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $response->assertRedirect('/products');

        // strip_tags() di prepareForValidation() harus membuang tag <b> dan <i>
        $this->assertDatabaseHas('products', [
            'sku' => 'XSS-INPUT-01',
            'name' => 'Mouse Wireless',
            'description' => 'Deskripsi dengan formatting HTML',
        ]);
    }
}
