<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar akun pengguna dengan filter dan pagination (Admin Only).
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $query = User::query();

        // Filter pencarian berdasarkan nama atau email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter peran / role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filter status aktif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan formulir pembuatan pengguna baru.
     */
    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validated();

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->role = $validated['role'];
        $user->is_active = $validated['is_active'];
        $user->save();

        ActivityLogger::log(
            'create_user',
            "Menambahkan pengguna baru: '{$user->name}' ({$user->email}) dengan peran {$user->role}",
            $user
        );

        return redirect()->route('users.index')
            ->with('success', "Pengguna '{$user->name}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan form edit pengguna.
     */
    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * Perbarui data pengguna dengan proteksi aturan bisnis BR9.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validated();
        $currentAdmin = Auth::user();

        // Aturan BR9: Admin tidak bisa mengubah role atau menonaktifkan akunnya sendiri
        if ($user->id === $currentAdmin->id) {
            if ($validated['role'] !== $user->role) {
                return back()
                    ->withInput()
                    ->with('error', 'Anda tidak dapat mengubah peran (role) akun Anda sendiri.');
            }
            if (!$validated['is_active']) {
                return back()
                    ->withInput()
                    ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }
        }

        // Aturan BR9: Minimal 1 admin aktif harus tetap ada di sistem
        if ($user->isAdmin() && $user->is_active) {
            $changingRoleAway = ($validated['role'] !== 'admin');
            $deactivating = (!$validated['is_active']);

            if ($changingRoleAway || $deactivating) {
                $otherActiveAdmins = User::where('role', 'admin')
                    ->where('is_active', true)
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($otherActiveAdmins < 1) {
                    return back()
                        ->withInput()
                        ->with('error', 'Tindakan ditolak. Minimal harus ada 1 Administrator aktif di sistem GudangKu.');
                }
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Jangan izinkan ubah role/status diri sendiri
        if ($user->id !== $currentAdmin->id) {
            $user->role = $validated['role'];
            $user->is_active = $validated['is_active'];
        }

        $user->save();

        ActivityLogger::log(
            'update_user',
            "Memperbarui data pengguna: '{$user->name}' ({$user->email})",
            $user
        );

        return redirect()->route('users.index')
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Hapus pengguna dengan proteksi aturan BR9 dan integritas riwayat transaksi.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $currentAdmin = Auth::user();

        // Aturan BR9: Tidak bisa menghapus diri sendiri
        if ($user->id === $currentAdmin->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Aturan BR9: Minimal 1 admin aktif
        if ($user->isAdmin()) {
            $otherActiveAdmins = User::where('role', 'admin')
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($otherActiveAdmins < 1) {
                return back()->with('error', 'Tidak dapat menghapus Administrator aktif terakhir.');
            }
        }

        // Cek integritas transaksi: jika sudah mencatat transaksi stok, jangan dihapus
        if ($user->stockTransactions()->exists()) {
            return back()->with(
                'error',
                "Pengguna '{$user->name}' tidak dapat dihapus karena tercatat dalam riwayat transaksi mutasi inventaris. Anda dapat menonaktifkan status akunnya sebagai gantinya."
            );
        }

        $userName = $user->name;
        $user->delete();

        ActivityLogger::log('delete_user', "Menghapus akun pengguna: '{$userName}'");

        return redirect()->route('users.index')
            ->with('success', "Pengguna '{$userName}' berhasil dihapus.");
    }
}
