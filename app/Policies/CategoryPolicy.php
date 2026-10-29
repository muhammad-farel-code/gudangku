<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Semua user login (termasuk Staff) boleh melihat daftar kategori.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Semua user login boleh melihat detail kategori.
     */
    public function view(User $user, Category $category): bool
    {
        return true;
    }

    /**
     * Hanya Admin dan Manager yang boleh membuat kategori baru.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Hanya Admin dan Manager yang boleh memperbarui kategori.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->hasRole('admin', 'manager');
    }

    /**
     * Hanya Admin dan Manager yang boleh menghapus kategori.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->hasRole('admin', 'manager');
    }
}
