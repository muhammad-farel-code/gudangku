<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama: Arahkan ke dashboard jika sudah login, atau ke login jika tamu
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Route Autentikasi Publik (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Route Berotentikasi & Akun Aktif
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // --- Master Data (CUD: Hanya Manager & Admin) ---
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
        Route::resource('suppliers', SupplierController::class)->except(['index', 'show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });

    // --- Master Data (Read: Semua role login) ---
    Route::resource('categories', CategoryController::class)->only(['index', 'show']);
    Route::resource('suppliers', SupplierController::class)->only(['index', 'show']);
    Route::resource('products', ProductController::class)->only(['index', 'show']);

    // --- Transaksi Stok (Penyesuaian: Khusus Manager & Admin) ---
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/stock/adjustment', [\App\Http\Controllers\StockAdjustmentController::class, 'create'])->name('stock.adjustment');
        Route::post('/stock/adjustment', [\App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('stock.adjustment.store');
    });

    // --- Transaksi Stok (Masuk & Keluar: Semua role login) ---
    Route::get('/stock/in', [\App\Http\Controllers\StockInController::class, 'create'])->name('stock.in');
    Route::post('/stock/in', [\App\Http\Controllers\StockInController::class, 'store'])->name('stock.in.store');

    Route::get('/stock/out', [\App\Http\Controllers\StockOutController::class, 'create'])->name('stock.out');
    Route::post('/stock/out', [\App\Http\Controllers\StockOutController::class, 'store'])->name('stock.out.store');

    // --- Riwayat & Detail Transaksi (Read-Only / Immutable: Semua role login) ---
    Route::get('/transactions', [\App\Http\Controllers\StockTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{stockTransaction}', [\App\Http\Controllers\StockTransactionController::class, 'show'])->name('transactions.show');
});
