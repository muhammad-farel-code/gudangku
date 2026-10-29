<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Standar password aman: minimal 8 karakter, huruf besar & kecil, serta angka
        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers();
        });

        // Rate limiter untuk proteksi brute force login: maksimal 5 percobaan per menit per email + IP
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        // Pagination menggunakan template Bootstrap 5
        Paginator::useBootstrapFive();
    }
}
