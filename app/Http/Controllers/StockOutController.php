<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreStockOutRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockOutController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Tampilkan formulir pencatatan barang keluar (OUT).
     */
    public function create(Request $request): View
    {
        Gate::authorize('createOut', StockTransaction::class);

        $products = Product::active()->with(['category', 'supplier'])->orderBy('name')->get();

        $selectedProduct = $request->filled('product_id')
            ? Product::with(['category', 'supplier'])->find($request->input('product_id'))
            : null;

        return view('stock.out', compact('products', 'selectedProduct'));
    }

    /**
     * Simpan transaksi stok keluar ke database secara atomik.
     */
    public function store(StoreStockOutRequest $request): RedirectResponse
    {
        Gate::authorize('createOut', StockTransaction::class);

        $validated = $request->validated();

        try {
            $transaction = $this->stockService->stockOut(
                product: (int) $validated['product_id'],
                quantity: (int) $validated['quantity'],
                user: $request->user(),
                recipient: $validated['recipient'],
                notes: $validated['notes'] ?? null,
                date: $validated['transaction_date'] ?? null
            );

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Stok keluar berhasil dicatat dengan nomor referensi {$transaction->reference_no}.");
        } catch (InsufficientStockException $e) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }
    }
}
