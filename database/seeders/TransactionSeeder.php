<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Jalankan seeder transaksi stok (~120 transaksi dalam 30 hari terakhir) menggunakan StockService.
     * Bersifat idempotent: jika transaksi sudah diisi lebih dari 10 data, lewati seeder ini.
     */
    public function run(): void
    {
        // Guard Idempotent: Cek apakah sudah ada transaksi yang terisi
        if (StockTransaction::count() >= 50) {
            $this->command?->info('TransactionSeeder dilewati: Transaksi sudah ada di database.');
            return;
        }

        $stockService = app(StockService::class);

        $users = User::all()->keyBy('role');
        $admin = $users->get('admin') ?? User::first();
        $manager = $users->get('manager') ?? $admin;
        $staff = $users->get('staff') ?? $admin;

        $products = Product::with(['category', 'supplier'])->get();
        $suppliers = Supplier::all();

        if ($products->isEmpty() || $suppliers->isEmpty()) {
            $this->command?->warn('TransactionSeeder: Tidak ada produk atau pemasok yang tersedia.');
            return;
        }

        $recipients = [
            'Divisi IT & Perangkat Keras',
            'Toko Cabang Jakarta Pusat',
            'Toko Cabang Surabaya',
            'Toko Cabang Bandung Barat',
            'Departemen Logistik & Pengiriman',
            'Gudang Transit Cikarang',
            'Klien B2B PT Solusi Pratama',
            'Divisi Operasional & HR',
            'Showroom Utama GudangKu',
            'Proyek Infrastruktur Jaringan',
        ];

        $inNotes = [
            'Penerimaan PO reguler vendor',
            'Restock inventaris awal pekan',
            'Pengiriman batch kedua',
            'Pengadaan berkala kuartal berjalan',
            'Tambahan stok buffer gudang',
            'Penerimaan retur barang kondisi baru',
        ];

        $outNotes = [
            'Permintaan unit kerja untuk operasional',
            'Distribusi mingguan ke toko cabang',
            'Pengiriman pesanan korporat',
            'Faktur penjualan langsung no. DO-2026',
            'Penggantian unit inventaris kantor',
        ];

        $adjNotes = [
            'Hasil audit stock opname mingguan',
            'Penyesuaian fisik barang di rak penyimpanan',
            'Koreksi selisih hitung fisik akhir bulan',
            'Koreksi kerusakan kemasan luar saat bongkar muat',
        ];

        $totalTransactions = 0;
        $startDate = Carbon::today()->subDays(30);

        // Loop 30 hari ke belakang secara kronologis
        for ($day = 30; $day >= 0; $day--) {
            $currentDate = Carbon::today()->subDays($day)->toDateString();
            
            // Buat 3 hingga 5 transaksi per hari
            $dailyCount = rand(3, 5);

            for ($i = 0; $i < $dailyCount; $i++) {
                if ($totalTransactions >= 120) {
                    break 2;
                }

                $product = $products->random();
                $product->refresh();

                // Tentukan tipe transaksi: 50% Masuk (IN), 40% Keluar (OUT), 10% Penyesuaian (ADJ)
                $dice = rand(1, 100);

                if ($dice <= 50) {
                    // STOK MASUK (IN)
                    $qty = rand(5, 50);
                    $supplier = $product->supplier ?? $suppliers->random();
                    $actor = rand(0, 1) ? $staff : $manager;

                    $stockService->stockIn(
                        product: $product,
                        quantity: $qty,
                        user: $actor,
                        supplierId: $supplier->id,
                        notes: $inNotes[array_rand($inNotes)],
                        date: $currentDate
                    );

                    $totalTransactions++;
                } elseif ($dice <= 90) {
                    // STOK KELUAR (OUT)
                    $currentStock = (int) $product->stock;

                    if ($currentStock > 3) {
                        // Ambil jumlah yang aman agar tidak minus
                        $maxOut = min($currentStock - 1, 20);
                        $qty = rand(1, max(1, $maxOut));
                        $actor = rand(0, 1) ? $staff : $manager;

                        try {
                            $stockService->stockOut(
                                product: $product,
                                quantity: $qty,
                                user: $actor,
                                recipient: $recipients[array_rand($recipients)],
                                notes: $outNotes[array_rand($outNotes)],
                                date: $currentDate
                            );

                            $totalTransactions++;
                        } catch (\Throwable $e) {
                            // Abaikan jika stok mendadak tidak cukup
                        }
                    }
                } else {
                    // PENYESUAIAN STOK (ADJUSTMENT) - Khusus Manager & Admin
                    $actor = rand(0, 1) ? $manager : $admin;
                    $currentStock = (int) $product->stock;
                    
                    // Selisih kecil (-3 s.d +3)
                    $delta = rand(-3, 3);
                    $actualStock = max(1, $currentStock + $delta);

                    $stockService->adjust(
                        product: $product,
                        actualStock: $actualStock,
                        user: $actor,
                        notes: $adjNotes[array_rand($adjNotes)],
                        date: $currentDate
                    );

                    $totalTransactions++;
                }
            }
        }

        $this->command?->info("TransactionSeeder berhasil mencatat {$totalTransactions} transaksi riil via StockService.");
    }
}
