<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman formulir profil pengguna.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Perbarui nama profil pengguna.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->save();

        ActivityLogger::log('update_profile', "Memperbarui data profil: {$user->name}", $user);

        return back()->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi pengguna dan regenerasi sesi login.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Regenerasi sesi untuk keamanan setelah perubahan kata sandi
        $request->session()->regenerate();

        ActivityLogger::log('update_password', "Memperbarui kata sandi akun: {$user->email}", $user);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}
