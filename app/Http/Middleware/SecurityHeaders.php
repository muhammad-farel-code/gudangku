<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Menambahkan security response headers untuk perlindungan browser hardening.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Cegah Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Cegah MIME-Type Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Kebijakan Referrer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Pembatasan API Browser Berbahaya
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS (HTTP Strict Transport Security) khusus lingkungan Production atau koneksi HTTPS
        if (app()->isProduction() || $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
