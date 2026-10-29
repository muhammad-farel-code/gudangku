<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    /**
     * Generate nomor referensi transaksi unik (Aturan BR12).
     * Format: IN-YYYYMMDD-0001 / OUT-YYYYMMDD-0001 / ADJ-YYYYMMDD-0001
     */
    public function generateReference(string $type, ?string $date = null): string
    {
        $prefix = match (strtolower($type)) {
            'in' => 'IN',
            'out' => 'OUT',
            'adjustment' => 'ADJ',
            default => strtoupper(substr($type, 0, 3)),
        };

        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $targetDate->format('Ymd');
        $pattern = "{$prefix}-{$dateStr}-%";

        // Ambil transaksi terakhir pada tanggal tersebut untuk menghitung urutan
        $lastRef = StockTransaction::where('reference_no', 'like', $pattern)
            ->orderByDesc('id')
            ->value('reference_no');

        if ($lastRef) {
            $parts = explode('-', $lastRef);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        $formattedSequence = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$dateStr}-{$formattedSequence}";
    }

    /**
     * Catat mutasi stok masuk (IN) secara atomik.
     *
     * @throws InvalidArgumentException
     */
    public function stockIn(
        Product|int $product,
        int $quantity,
        User $user,
        ?int $supplierId = null,
        ?string $notes = null,
        ?string $date = null
    ): StockTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah stok masuk harus lebih dari 0.');
        }

        $productId = $product instanceof Product ? $product->id : $product;
        $transactionDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        return DB::transaction(function () use ($productId, $quantity, $user, $supplierId, $notes, $transactionDate) {
            // Pessimistic Row Lock (Aturan BR3)
            $lockedProduct = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore + $quantity;

            // Perbarui stok fisik produk (Aturan BR2)
            $lockedProduct->update(['stock' => $stockAfter]);

            $referenceNo = $this->generateReference('in', $transactionDate);

            // Simpan riwayat transaksi immutable
            $transaction = StockTransaction::create([
                'reference_no' => $referenceNo,
                'product_id' => $lockedProduct->id,
                'user_id' => $user->id,
                'supplier_id' => $supplierId ?? $lockedProduct->supplier_id,
                'type' => 'in',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'recipient' => null,
                'transaction_date' => $transactionDate,
                'notes' => $notes,
            ]);

            // Jejak audit
            ActivityLogger::log(
                'stock_in',
                "Stok Masuk: {$quantity} {$lockedProduct->unit} untuk '{$lockedProduct->name}' (No. Ref: {$referenceNo})",
                $transaction,
                $user->id
            );

            return $transaction;
        });
    }

    /**
     * Catat mutasi stok keluar (OUT) secara atomik dengan pencegahan stok minus.
     *
     * @throws InvalidArgumentException
     * @throws InsufficientStockException
     */
    public function stockOut(
        Product|int $product,
        int $quantity,
        User $user,
        string $recipient,
        ?string $notes = null,
        ?string $date = null
    ): StockTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah stok keluar harus lebih dari 0.');
        }

        $productId = $product instanceof Product ? $product->id : $product;
        $transactionDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        return DB::transaction(function () use ($productId, $quantity, $user, $recipient, $notes, $transactionDate) {
            // Pessimistic Row Lock (Aturan BR3)
            $lockedProduct = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $stockBefore = $lockedProduct->stock;

            // Pengecekan stok mencukupi (Aturan BR1: Stok tidak boleh minus)
            if ($stockBefore < $quantity) {
                throw new InsufficientStockException($lockedProduct, $quantity, $stockBefore);
            }

            $stockAfter = $stockBefore - $quantity;

            // Perbarui stok fisik produk (Aturan BR2)
            $lockedProduct->update(['stock' => $stockAfter]);

            $referenceNo = $this->generateReference('out', $transactionDate);

            // Simpan riwayat transaksi immutable
            $transaction = StockTransaction::create([
                'reference_no' => $referenceNo,
                'product_id' => $lockedProduct->id,
                'user_id' => $user->id,
                'supplier_id' => null,
                'type' => 'out',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'recipient' => $recipient,
                'transaction_date' => $transactionDate,
                'notes' => $notes,
            ]);

            // Jejak audit
            ActivityLogger::log(
                'stock_out',
                "Stok Keluar: {$quantity} {$lockedProduct->unit} untuk '{$lockedProduct->name}' kepada {$recipient} (No. Ref: {$referenceNo})",
                $transaction,
                $user->id
            );

            return $transaction;
        });
    }

    /**
     * Catat penyesuaian stok fisik / stock opname (ADJUSTMENT).
     *
     * @throws InvalidArgumentException
     */
    public function adjust(
        Product|int $product,
        int $actualStock,
        User $user,
        string $notes,
        ?string $date = null
    ): StockTransaction {
        if ($actualStock < 0) {
            throw new InvalidArgumentException('Stok fisik hasil penyesuaian tidak boleh bernilai negatif.');
        }

        $productId = $product instanceof Product ? $product->id : $product;
        $transactionDate = $date ? Carbon::parse($date)->toDateString() : Carbon::today()->toDateString();

        return DB::transaction(function () use ($productId, $actualStock, $user, $notes, $transactionDate) {
            // Pessimistic Row Lock (Aturan BR3)
            $lockedProduct = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

            $stockBefore = $lockedProduct->stock;
            $stockAfter = $actualStock;
            $difference = abs($stockAfter - $stockBefore);

            // Perbarui stok fisik produk (Aturan BR2)
            $lockedProduct->update(['stock' => $stockAfter]);

            $referenceNo = $this->generateReference('adjustment', $transactionDate);

            // Simpan riwayat transaksi immutable
            $transaction = StockTransaction::create([
                'reference_no' => $referenceNo,
                'product_id' => $lockedProduct->id,
                'user_id' => $user->id,
                'supplier_id' => null,
                'type' => 'adjustment',
                'quantity' => $difference,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'recipient' => null,
                'transaction_date' => $transactionDate,
                'notes' => $notes,
            ]);

            // Jejak audit
            ActivityLogger::log(
                'stock_adjustment',
                "Penyesuaian Stok: '{$lockedProduct->name}' disesuaikan dari {$stockBefore} menjadi {$stockAfter} (Selisih: {$difference} {$lockedProduct->unit}, No. Ref: {$referenceNo})",
                $transaction,
                $user->id
            );

            return $transaction;
        });
    }
}
