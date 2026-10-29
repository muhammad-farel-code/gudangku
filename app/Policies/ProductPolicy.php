<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Semua pengguna terotentikasi (admin, manager, staff) dapat melihat daftar produk.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua pengguna terotentikasi dapat melihat detail produk.
     */
    public function view(User $user, Product $product): bool
    {
        return true;
    }

    /**
     * Hanya admin dan manager yang dapat menambah produk baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Hanya admin dan manager yang dapat memperbarui data produk.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Hanya admin dan manager yang dapat menghapus produk.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Helper policy untuk memeriksa apakah pengguna berhak melihat harga beli produk.
     * Hanya manager dan admin yang diizinkan melihat purchase_price.
     */
    public function viewPurchasePrice(User $user): bool
    {
        return $user->hasRole('admin', 'manager');
    }
}
