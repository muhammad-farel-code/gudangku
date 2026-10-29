<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Tampilkan formulir penyesuaian stok opname (ADJUSTMENT).
     */
    public function create(Request $request): View
    {
        Gate::authorize('adjust', StockTransaction::class);

        $products = Product::with(['category', 'supplier'])->orderBy('name')->get();

        $selectedProduct = $request->filled('product_id')
            ? Product::with(['category', 'supplier'])->find($request->input('product_id'))
            : null;

        return view('stock.adjustment', compact('products', 'selectedProduct'));
    }

    /**
     * Simpan penyesuaian stok opname secara atomik.
     */
    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        Gate::authorize('adjust', StockTransaction::class);

        $validated = $request->validated();

        $transaction = $this->stockService->adjust(
            product: (int) $validated['product_id'],
            actualStock: (int) $validated['actual_stock'],
            user: $request->user(),
            notes: $validated['notes'],
            date: $validated['transaction_date'] ?? null
        );

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Penyesuaian stok berhasil dicatat dengan nomor referensi {$transaction->reference_no}.");
    }
}
