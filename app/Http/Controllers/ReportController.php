<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman laporan stok inventaris.
     */
    public function stock(Request $request): View
    {
        $query = Product::with(['category', 'supplier']);

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
            } elseif ($status === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        $products = $query->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $totalUnits = $products->sum('stock');
        $totalValue = $products->sum(fn ($p) => $p->stock * $p->purchase_price);

        return view('reports.stock', compact('products', 'categories', 'totalUnits', 'totalValue'));
    }

    /**
     * Export laporan stok ke file CSV aman dari formula injection.
     */
    public function exportStock(Request $request): StreamedResponse
    {
        $query = Product::with(['category', 'supplier']);

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
            } elseif ($status === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        $products = $query->orderBy('name')->get();
        $filename = 'laporan-stok-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Excel membukanya dengan encoding yang benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                'No',
                'Kode SKU',
                'Nama Produk',
                'Kategori',
                'Pemasok Utama',
                'Stok Fisik',
                'Satuan',
                'Batas Min. Stok',
                'Harga Beli (Rp)',
                'Harga Jual (Rp)',
                'Nilai Persediaan (Rp)',
                'Lokasi Rak',
                'Status Stok',
            ]);

            $no = 1;
            foreach ($products as $p) {
                fputcsv($handle, [
                    $no++,
                    $this->escapeCsv($p->sku),
                    $this->escapeCsv($p->name),
                    $this->escapeCsv($p->category->name ?? '-'),
                    $this->escapeCsv($p->supplier->name ?? '-'),
                    $p->stock,
                    $this->escapeCsv($p->unit),
                    $p->min_stock,
                    $p->purchase_price,
                    $p->selling_price,
                    $p->stock * $p->purchase_price,
                    $this->escapeCsv($p->rack_location ?: '-'),
                    $p->stock_status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Tampilkan halaman laporan riwayat transaksi stok.
     */
    public function transactions(Request $request): View
    {
        $query = StockTransaction::with(['product.category', 'user', 'supplier']);

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $transactions = $query->orderByDesc('transaction_date')->orderByDesc('id')->get();
        $products = Product::orderBy('name')->get();

        $totalIn = $transactions->where('type', 'in')->sum('quantity');
        $totalOut = $transactions->where('type', 'out')->sum('quantity');

        return view('reports.transactions', compact('transactions', 'products', 'totalIn', 'totalOut'));
    }

    /**
     * Export laporan transaksi ke file CSV aman dari formula injection.
     */
    public function exportTransactions(Request $request): StreamedResponse
    {
        $query = StockTransaction::with(['product.category', 'user', 'supplier']);

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $transactions = $query->orderByDesc('transaction_date')->orderByDesc('id')->get();
        $filename = 'laporan-transaksi-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                'No',
                'No. Referensi',
                'Tanggal',
                'Tipe Transaksi',
                'Kode SKU',
                'Nama Produk',
                'Jumlah',
                'Satuan',
                'Stok Sebelum',
                'Stok Sesudah',
                'Pihak Terkait (Pemasok / Penerima)',
                'Dicatat Oleh',
                'Catatan / Keterangan',
            ]);

            $no = 1;
            foreach ($transactions as $t) {
                $party = match ($t->type) {
                    'in' => $t->supplier->name ?? '-',
                    'out' => $t->recipient ?: '-',
                    default => 'Penyesuaian Opname',
                };

                fputcsv($handle, [
                    $no++,
                    $this->escapeCsv($t->reference_no),
                    $t->transaction_date->format('Y-m-d'),
                    strtoupper($t->type),
                    $this->escapeCsv($t->product->sku ?? '-'),
                    $this->escapeCsv($t->product->name ?? '-'),
                    $t->quantity,
                    $this->escapeCsv($t->product->unit ?? '-'),
                    $t->stock_before,
                    $t->stock_after,
                    $this->escapeCsv($party),
                    $this->escapeCsv($t->user->name ?? '-'),
                    $this->escapeCsv($t->notes ?: '-'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Tampilan cetak ramah printer untuk laporan stok.
     */
    public function printStock(Request $request): View
    {
        $query = Product::with(['category', 'supplier']);

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            if ($status === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock')->where('stock', '>', 0);
            } elseif ($status === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        $products = $query->orderBy('name')->get();
        $totalUnits = $products->sum('stock');
        $totalValue = $products->sum(fn ($p) => $p->stock * $p->purchase_price);

        return view('reports.print_stock', compact('products', 'totalUnits', 'totalValue'));
    }

    /**
     * Tampilan cetak ramah printer untuk laporan transaksi.
     */
    public function printTransactions(Request $request): View
    {
        $query = StockTransaction::with(['product.category', 'user', 'supplier']);

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        $transactions = $query->orderByDesc('transaction_date')->orderByDesc('id')->get();
        $totalIn = $transactions->where('type', 'in')->sum('quantity');
        $totalOut = $transactions->where('type', 'out')->sum('quantity');

        return view('reports.print_transactions', compact('transactions', 'totalIn', 'totalOut'));
    }

    /**
     * Netralkan CSV Formula Injection:
     * Awali sel yang dimulai dengan =, +, -, @ dengan tanda petik tunggal.
     */
    protected function escapeCsv(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $firstChar = substr($value, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
