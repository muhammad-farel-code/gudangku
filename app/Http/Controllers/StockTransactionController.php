<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockTransactionController extends Controller
{
    /**
     * Tampilkan riwayat transaksi mutasi stok dengan filter komprehensif.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', StockTransaction::class);

        $query = StockTransaction::with(['product.category', 'user', 'supplier']);

        // Filter pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhere('recipient', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter tipe transaksi (in, out, adjustment)
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter produk tertentu
        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        // Filter rentang tanggal transaksi
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        $transactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'products'));
    }

    /**
     * Tampilkan detail mutasi transaksi stok.
     */
    public function show(StockTransaction $stockTransaction): View
    {
        Gate::authorize('view', $stockTransaction);

        $stockTransaction->load(['product.category', 'user', 'supplier']);

        return view('transactions.show', compact('stockTransaction'));
    }
}
