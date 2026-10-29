<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
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

    public function test_all_authenticated_roles_can_view_category_index_and_show(): void
    {
        $category = Category::create([
            'name' => 'Kategori Uji',
            'description' => 'Deskripsi uji coba',
        ]);

        // Staff bisa melihat index dan show
        $responseStaff = $this->actingAs($this->staff)->get(route('categories.index'));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee('Kategori Uji');

        $responseShow = $this->actingAs($this->staff)->get(route('categories.show', $category));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Kategori Uji');
    }

    public function test_staff_cannot_access_create_or_store_category_returns_403(): void
    {
        $responseCreate = $this->actingAs($this->staff)->get(route('categories.create'));
        $responseCreate->assertStatus(403);

        $responseStore = $this->actingAs($this->staff)->post(route('categories.store'), [
            'name' => 'Kategori Ilegal',
            'description' => 'Tidak boleh disimpan',
        ]);
        $responseStore->assertStatus(403);
    }

    public function test_manager_and_admin_can_store_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Peralatan Medis',
            'description' => 'Alat-alat kesehatan dan sanitasi',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Peralatan Medis']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'create_category']);
    }

    public function test_store_category_validates_uniqueness_and_xss_sanitization(): void
    {
        Category::create(['name' => 'Elektronik']);

        // Uji duplikat nama
        $response = $this->actingAs($this->manager)->post(route('categories.store'), [
            'name' => 'Elektronik',
        ]);

        $response->assertSessionHasErrors('name');

        // Uji sanitasi strip_tags
        $this->actingAs($this->manager)->post(route('categories.store'), [
            'name' => '<script>alert(1)</script>Kategori Bersih',
            'description' => '<b>Deskripsi</b> aman',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'alert(1)Kategori Bersih',
            'description' => 'Deskripsi aman',
        ]);
    }

    public function test_manager_can_update_category(): void
    {
        $category = Category::create(['name' => 'Kategori Lama']);

        $response = $this->actingAs($this->manager)->put(route('categories.update', $category), [
            'name' => 'Kategori Baru',
            'description' => 'Deskripsi diperbarui',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Kategori Baru']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'update_category']);
    }

    public function test_cannot_delete_category_with_products_br5_protection(): void
    {
        $category = Category::create(['name' => 'Kategori Berisi']);

        // Tambah produk di bawah kategori ini
        Product::create([
            'category_id' => $category->id,
            'sku' => 'TEST-001',
            'name' => 'Produk Penjaga',
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 10,
            'min_stock' => 2,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        // Harus dicegah (redirect back dengan error, bukan HTTP 500)
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_can_delete_empty_category(): void
    {
        $category = Category::create(['name' => 'Kategori Kosong']);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'delete_category']);
    }
}
