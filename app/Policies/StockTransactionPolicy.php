<?php

namespace App\Policies;

use App\Models\StockTransaction;
use App\Models\User;

class StockTransactionPolicy
{
    /**
     * Semua pengguna terotentikasi dapat melihat riwayat transaksi stok.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua pengguna terotentikasi dapat melihat detail transaksi stok.
     */
    public function view(User $user, StockTransaction $stockTransaction): bool
    {
        return true;
    }

    /**
     * Semua pengguna terotentikasi (admin, manager, staff) dapat mencatat stok masuk.
     */
    public function createIn(User $user): bool
    {
        return true;
    }

    /**
     * Semua pengguna terotentikasi (admin, manager, staff) dapat mencatat stok keluar.
     */
    public function createOut(User $user): bool
    {
        return true;
    }

    /**
     * Hanya admin dan manager yang berwenang melakukan penyesuaian stok opname.
     */
    public function adjust(User $user): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Transaksi stok bersifat IMMUTABLE. Tidak ada pengguna yang boleh mengedit transaksi.
     */
    public function update(User $user, StockTransaction $stockTransaction): bool
    {
        return false;
    }

    /**
     * Transaksi stok bersifat IMMUTABLE. Tidak ada pengguna yang boleh menghapus transaksi.
     */
    public function delete(User $user, StockTransaction $stockTransaction): bool
    {
        return false;
    }
}
