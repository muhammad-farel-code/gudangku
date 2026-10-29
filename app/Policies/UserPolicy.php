<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Hanya admin yang dapat melihat daftar pengguna.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang dapat melihat detail pengguna.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang dapat membuat pengguna baru.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Hanya admin yang dapat mengedit pengguna.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Aturan BR9: Admin tidak bisa menghapus dirinya sendiri,
     * dan minimal 1 admin aktif harus tetap ada di sistem.
     */
    public function delete(User $user, User $model): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        // Tidak boleh menghapus diri sendiri
        if ($user->id === $model->id) {
            return false;
        }

        // Jika target adalah admin, pastikan masih ada minimal 1 admin aktif lainnya
        if ($model->isAdmin()) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $model->id)
                ->count();

            if ($otherActiveAdmins < 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Aturan BR9: Admin tidak bisa mengubah role dirinya sendiri.
     */
    public function updateRole(User $user, User $model): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->id !== $model->id;
    }

    /**
     * Aturan BR9: Admin tidak bisa menonaktifkan dirinya sendiri.
     */
    public function toggleActive(User $user, User $model): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        // Tidak boleh menonaktifkan diri sendiri
        if ($user->id === $model->id) {
            return false;
        }

        // Jika target adalah admin, pastikan masih ada admin aktif lainnya
        if ($model->isAdmin() && $model->is_active) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $model->id)
                ->count();

            if ($otherActiveAdmins < 1) {
                return false;
            }
        }

        return true;
    }
}
