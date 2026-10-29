<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard utama GudangKu.
     * Menggunakan Query Builder untuk agregasi performa tinggi dan Eloquent untuk daftar entitas.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // --- 1. AGREGASI VIA QUERY BUILDER ---
        $totalProducts = DB::table('products')->where('is_active', true)->count();
        $totalStockUnits = (int) DB::table('products')->sum('stock');
        $lowStockCount = DB::table('products')->whereColumn('stock', '<=', 'min_stock')->count();
        $outOfStockCount = DB::table('products')->where('stock', '<=', 0)->count();

        // Rasio persentase produk menipis untuk gauge SVG
        $lowStockPercentage = $totalProducts > 0
            ? (int) round(($lowStockCount / $totalProducts) * 100)
            : 0;

        // Pembatasan Akses Finansial (Staff tidak menerima data nilai persediaan)
        $isManagerOrAdmin = $user && $user->hasRole('admin', 'manager');
        $inventoryValue = null;
        $todayTransactionsCount = null;

        if ($isManagerOrAdmin) {
            $inventoryValue = (float) (DB::table('products')
                ->selectRaw('SUM(stock * purchase_price) as total_val')
                ->value('total_val') ?? 0);
        } else {
            $todayTransactionsCount = DB::table('stock_transactions')
                ->whereDate('transaction_date', Carbon::today())
                ->count();
        }

        // --- 2. TREN 14 HARI TERAKHIR (STOK MASUK VS KELUAR) ---
        $startDate = Carbon::today()->subDays(13);
        $endDate = Carbon::today();

        $txStats = DB::table('stock_transactions')
            ->select('transaction_date', 'type', DB::raw('SUM(quantity) as total_qty'))
            ->where('transaction_date', '>=', $startDate->toDateString())
            ->where('transaction_date', '<=', $endDate->toDateString())
            ->whereIn('type', ['in', 'out'])
            ->groupBy('transaction_date', 'type')
            ->get();

        // Indexing data berdasarkan "YYYY-MM-DD_type"
        $lookup = [];
        foreach ($txStats as $row) {
            $lookup[$row->transaction_date . '_' . $row->type] = (int) $row->total_qty;
        }

        $labels = [];
        $stockInTrend = [];
        $stockOutTrend = [];

        // Pastikan setiap hari dari 14 hari terakhir terisi (isi 0 jika tanpa transaksi)
        for ($i = 13; $i >= 0; $i--) {
            $dateObj = Carbon::today()->subDays($i);
            $dateStr = $dateObj->toDateString();
            $labels[] = $dateObj->translatedFormat('d M');
            $stockInTrend[] = $lookup[$dateStr . '_in'] ?? 0;
            $stockOutTrend[] = $lookup[$dateStr . '_out'] ?? 0;
        }

        $chartData = [
            'labels' => $labels,
            'stockIn' => $stockInTrend,
            'stockOut' => $stockOutTrend,
        ];

        // --- 3. DAFTAR ENTITAS VIA ELOQUENT (Eager Loading, Bebas N+1) ---
        // Tab Chip Produk (Semua, Menipis, Habis)
        $allProducts = Product::with('category')->latest()->limit(5)->get();
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->latest()
            ->limit(5)
            ->get();
        $outOfStockProducts = Product::with('category')
            ->where('stock', '<=', 0)
            ->latest()
            ->limit(5)
            ->get();

        // 8 Transaksi Terbaru
        $recentTransactions = StockTransaction::with(['product.category', 'user'])
            ->latest('transaction_date')
            ->latest('id')
            ->limit(8)
            ->get();

        // Rekomendasi Restock (Berbasis Aturan: (min_stock * 2) - stock)
        $restockRecommendations = Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderByRaw('(stock / GREATEST(min_stock, 1)) ASC')
            ->limit(5)
            ->get()
            ->map(function ($prod) {
                $suggested = max(1, ($prod->min_stock * 2) - $prod->stock);
                $prod->suggested_order = $suggested;
                return $prod;
            });

        // Data payload view
        $viewData = [
            'totalProducts' => $totalProducts,
            'totalStockUnits' => $totalStockUnits,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'lowStockPercentage' => $lowStockPercentage,
            'chartData' => $chartData,
            'allProducts' => $allProducts,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'recentTransactions' => $recentTransactions,
            'restockRecommendations' => $restockRecommendations,
            'isManagerOrAdmin' => $isManagerOrAdmin,
        ];

        // Data nilai persediaan HANYA dikirimkan jika user adalah manager atau admin
        if ($isManagerOrAdmin) {
            $viewData['inventoryValue'] = $inventoryValue;
        } else {
            $viewData['todayTransactionsCount'] = $todayTransactionsCount;
        }

        return view('dashboard', $viewData);
    }
}
