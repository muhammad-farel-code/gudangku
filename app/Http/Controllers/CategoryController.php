<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori dengan pencarian dan pagination.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Category::class);

        $query = Category::query()->withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->latest()->paginate(10)->withQueryString();

        return view('categories.index', compact('categories'));
    }

    /**
     * Menampilkan form penambahan kategori.
     */
    public function create(): View
    {
        Gate::authorize('create', Category::class);

        return view('categories.create');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $category = Category::create($request->validated());

        // Audit trail
        ActivityLogger::log('create_category', 'Menambahkan kategori baru: ' . $category->name, $category);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $category->name . '" berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail kategori beserta produk terkait.
     */
    public function show(Category $category): View
    {
        Gate::authorize('view', $category);

        $category->loadCount('products');
        $products = $category->products()->latest()->paginate(10)->withQueryString();

        return view('categories.show', compact('category', 'products'));
    }

    /**
     * Menampilkan form edit kategori.
     */
    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.edit', compact('category'));
    }

    /**
     * Memperbarui data kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $category->update($request->validated());

        // Audit trail
        ActivityLogger::log('update_category', 'Memperbarui kategori: ' . $category->name, $category);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $category->name . '" berhasil diperbarui.');
    }

    /**
     * Menghapus kategori dengan penerapan aturan bisnis BR5.
     */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        // BR5: Kategori tidak bisa dihapus bila masih memiliki produk terkait
        if ($category->products()->exists()) {
            return back()->with(
                'error',
                'Kategori "' . $category->name . '" tidak dapat dihapus karena masih memuat produk terkait. Pindahkan atau hapus produk terlebih dahulu.'
            );
        }

        $categoryName = $category->name;
        $category->delete();

        // Audit trail
        ActivityLogger::log('delete_category', 'Menghapus kategori: ' . $categoryName);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "' . $categoryName . '" berhasil dihapus.');
    }
}
