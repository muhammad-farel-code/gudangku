<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    /**
     * Semua pengguna login boleh melihat daftar pemasok.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua pengguna login boleh melihat detail pemasok.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return true;
    }

    /**
     * Hanya Admin dan Manager yang boleh membuat pemasok baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Hanya Admin dan Manager yang boleh memperbarui pemasok.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Hanya Admin dan Manager yang boleh menghapus pemasok.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasRole('admin', 'manager');
    }
}
