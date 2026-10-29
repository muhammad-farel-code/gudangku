<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Tampilkan daftar produk dengan pencarian, filter kategori, filter stok menipis, dan pagination.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);

        $query = Product::with(['category', 'supplier']);

        // Filter pencarian berdasarkan nama atau SKU
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter stok menipis (scopeLowStock)
        if ($request->boolean('low_stock') || $request->input('stock_status') === 'low') {
            $query->lowStock();
        }

        // Filter status aktif/tidak aktif jika ada
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Tampilkan form untuk membuat produk baru.
     */
    public function create(): View
    {
        Gate::authorize('create', Product::class);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Simpan produk baru ke database (dengan initial_stock jika diisi).
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);

        $validated = $request->validated();

        // Tangani initial_stock (default 0)
        $initialStock = (int) ($validated['initial_stock'] ?? 0);
        $validated['stock'] = $initialStock;
        unset($validated['initial_stock']);

        $product = Product::create($validated);

        ActivityLogger::log('create', "Menambahkan produk baru: {$product->name} (SKU: {$product->sku})", $product);

        return redirect()->route('products.index')
            ->with('success', "Produk '{$product->name}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan detail produk dan riwayat transaksi stok terakhir.
     */
    public function show(Product $product): View
    {
        Gate::authorize('view', $product);

        $product->load([
            'category',
            'supplier',
            'stockTransactions' => fn ($query) => $query->with('user')->latest()->limit(10),
        ]);

        return view('products.show', compact('product'));
    }

    /**
     * Tampilkan form edit data produk.
     * Aturan BR2: Stok tidak dapat diedit melalui form ini.
     */
    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Perbarui data produk. Field stock tidak disertakan sesuai BR2.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $validated = $request->validated();
        $product->update($validated);

        ActivityLogger::log('update', "Memperbarui data produk: {$product->name} (SKU: {$product->sku})", $product);

        return redirect()->route('products.index')
            ->with('success', "Data produk '{$product->name}' berhasil diperbarui.");
    }

    /**
     * Hapus data produk jika belum memiliki riwayat transaksi (Aturan BR6).
     */
    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        // Aturan BR6: Cek apakah produk sudah pernah ditransaksikan
        if ($product->stockTransactions()->exists()) {
            return back()->with('error', "Produk '{$product->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi stok. Anda dapat menonaktifkan status produk ini jika sudah tidak digunakan.");
        }

        $productName = $product->name;
        $productSku = $product->sku;

        $product->delete();

        ActivityLogger::log('delete', "Menghapus produk: {$productName} (SKU: {$productSku})", $product);

        return redirect()->route('products.index')
            ->with('success', "Produk '{$productName}' berhasil dihapus.");
    }
}
