<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk semua respons (portal, panel Admin, API) — SEC-03:
 * - halaman tidak boleh dimuat di dalam <iframe> situs lain (clickjacking);
 * - browser tidak menebak tipe konten (MIME sniffing);
 * - URL lengkap tidak bocor ke situs lain lewat header Referer;
 * - fitur perangkat (kamera, mikrofon, lokasi, dll.) dinonaktifkan karena tidak dipakai;
 * - versi PHP (X-Powered-By) tidak diumumkan;
 * - HSTS hanya di produksi lewat HTTPS, agar pengembangan lokal via HTTP tidak terganggu.
 *
 * CSP penuh (script/style) belum diterapkan: Filament & Livewire memakai script inline, sehingga
 * perlu diuji tersendiri. "frame-ancestors" aman diterapkan karena tidak membatasi script.
 */
final class SecurityHeaders
{
    public const HSTS = 'max-age=31536000; includeSubDomains';

    /** @var array<string, string> */
    public const HEADERS = [
        'X-Frame-Options' => 'DENY',
        'Content-Security-Policy' => "frame-ancestors 'none'",
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::HEADERS as $nama => $nilai) {
            $response->headers->set($nama, $nilai);
        }

        // Versi PHP tidak perlu diketahui klien (ditambahkan PHP sendiri, bukan oleh Laravel).
        $response->headers->remove('X-Powered-By');
        if (! headers_sent()) {
            header_remove('X-Powered-By');
        }

        if (App::isProduction() && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', self::HSTS);
        }

        return $response;
    }
}
