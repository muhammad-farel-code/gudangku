<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockInRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockInController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Tampilkan formulir pencatatan barang masuk (IN).
     */
    public function create(Request $request): View
    {
        Gate::authorize('createIn', StockTransaction::class);

        $products = Product::active()->with(['category', 'supplier'])->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $selectedProduct = $request->filled('product_id')
            ? Product::with(['category', 'supplier'])->find($request->input('product_id'))
            : null;

        return view('stock.in', compact('products', 'suppliers', 'selectedProduct'));
    }

    /**
     * Simpan transaksi stok masuk ke database secara atomik.
     */
    public function store(StoreStockInRequest $request): RedirectResponse
    {
        Gate::authorize('createIn', StockTransaction::class);

        $validated = $request->validated();

        $transaction = $this->stockService->stockIn(
            product: (int) $validated['product_id'],
            quantity: (int) $validated['quantity'],
            user: $request->user(),
            supplierId: !empty($validated['supplier_id']) ? (int) $validated['supplier_id'] : null,
            notes: $validated['notes'] ?? null,
            date: $validated['transaction_date'] ?? null
        );

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Stok masuk berhasil dicatat dengan nomor referensi {$transaction->reference_no}.");
    }
}
