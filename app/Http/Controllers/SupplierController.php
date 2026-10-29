<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Menampilkan daftar pemasok dengan fitur pencarian dan pagination.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Supplier::class);

        $query = Supplier::query()->withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Menampilkan form penambahan pemasok baru.
     */
    public function create(): View
    {
        Gate::authorize('create', Supplier::class);

        return view('suppliers.create');
    }

    /**
     * Menyimpan pemasok baru ke database.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Gate::authorize('create', Supplier::class);

        $supplier = Supplier::create($request->validated());

        // Audit trail
        ActivityLogger::log('create_supplier', 'Menambahkan pemasok baru: ' . $supplier->name, $supplier);

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok "' . $supplier->name . '" berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail informasi pemasok dan produk yang dipasok.
     */
    public function show(Supplier $supplier): View
    {
        Gate::authorize('view', $supplier);

        $supplier->loadCount('products');
        $products = $supplier->products()->latest()->paginate(10)->withQueryString();

        return view('suppliers.show', compact('supplier', 'products'));
    }

    /**
     * Menampilkan form edit data pemasok.
     */
    public function edit(Supplier $supplier): View
    {
        Gate::authorize('update', $supplier);

        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Memperbarui informasi pemasok.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('update', $supplier);

        $supplier->update($request->validated());

        // Audit trail
        ActivityLogger::log('update_supplier', 'Memperbarui pemasok: ' . $supplier->name, $supplier);

        return redirect()->route('suppliers.index')
            ->with('success', 'Data pemasok "' . $supplier->name . '" berhasil diperbarui.');
    }

    /**
     * Menghapus pemasok (Aturan Bisnis BR7: relasi produk & transaksi otomatis SET NULL).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        Gate::authorize('delete', $supplier);

        $supplierName = $supplier->name;
        $supplier->delete();

        // Audit trail
        ActivityLogger::log('delete_supplier', 'Menghapus pemasok: ' . $supplierName);

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok "' . $supplierName . '" berhasil dihapus (riwayat transaksi dan produk terkait tetap aman).');
    }
}
