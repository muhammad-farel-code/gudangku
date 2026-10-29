<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan form login.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi pengguna (manual).
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        // Autentikasi dengan syarat tambahan akun harus aktif (is_active = true)
        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ])) {
            // Regenerasi ID sesi untuk mencegah serangan Session Fixation
            $request->session()->regenerate();

            // Catat log aktivitas masuk
            ActivityLogger::log('login', 'Pengguna berhasil masuk: ' . Auth::user()->email);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        // Pesan gagal generik untuk mencegah User Enumeration
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
    }

    /**
     * Menampilkan form pendaftaran publik.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Memproses pendaftaran user baru (role selalu 'staff').
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);

        // Keamanan Mass Assignment: Role 'staff' dan is_active diset eksplisit dari server,
        // tidak pernah mempercayai input request publik.
        $user->role = 'staff';
        $user->is_active = true;
        $user->save();

        // Catat aktivitas pendaftaran
        ActivityLogger::log('register', 'Akun staf baru berhasil didaftarkan: ' . $user->email, $user, $user->id);

        // Langsung autentikasi pengguna baru dan regenerasi sesi
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Pendaftaran berhasil! Selamat datang di Sistem GudangKu.');
    }

    /**
     * Memproses logout pengguna (hanya via POST).
     */
    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            ActivityLogger::log('logout', 'Pengguna keluar dari sistem: ' . Auth::user()->email);
        }

        // Hapus autentikasi dari memori
        Auth::logout();

        // Hanguskan data sesi di database dan perbarui token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
