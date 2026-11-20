<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk semua respons (portal, panel Admin, API) — SEC-03:
 * - Content-Security-Policy: sumber daya hanya dari origin sendiri, tanpa plugin/<object>,
 *   <base> dan tujuan form dikunci ke origin sendiri, tidak boleh di-iframe situs lain;
 * - browser tidak menebak tipe konten (MIME sniffing);
 * - URL lengkap tidak bocor ke situs lain lewat header Referer;
 * - fitur perangkat (kamera, mikrofon, lokasi, dll.) dinonaktifkan karena tidak dipakai;
 * - versi PHP (X-Powered-By) tidak diumumkan;
 * - HSTS hanya di produksi lewat HTTPS, agar pengembangan lokal via HTTP tidak terganggu.
 *
 * Pengecualian CSP yang disengaja (by-design):
 * - script-src 'unsafe-inline' 'unsafe-eval': Livewire & Filament menyisipkan <script> inline,
 *   dan Alpine.js (bawaan Livewire) mengevaluasi ekspresi x-data/x-on lewat new Function().
 *   Migrasi ke nonce + build Alpine CSP belum didukung penuh oleh Filament.
 * - style-src 'unsafe-inline': atribut style="" dan <style> inline dari Filament/Livewire.
 * - img-src https://ui-avatars.com: avatar inisial bawaan Filament di menu akun.
 * XSS tetap dicegah oleh escaping Blade ({{ }}) di seluruh view.
 */
final class SecurityHeaders
{
    public const HSTS = 'max-age=31536000; includeSubDomains';

    /** @var array<string, list<string>> */
    public const CSP = [
        'default-src' => ["'self'"],
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", 'data:', 'https://ui-avatars.com'],
        'font-src' => ["'self'", 'data:'],
        'connect-src' => ["'self'"],
        'media-src' => ["'self'"],
        'object-src' => ["'none'"],
        'frame-src' => ["'self'"],
        'worker-src' => ["'self'"],
        'manifest-src' => ["'self'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'none'"],
    ];

    /** Directive yang perlu mengizinkan dev server Vite (`npm run dev`) saat berjalan. */
    private const DIRECTIVE_VITE = ['script-src', 'style-src', 'img-src', 'font-src', 'connect-src'];

    /** @var array<string, string> */
    public const HEADERS = [
        'X-Frame-Options' => 'DENY',
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

        $response->headers->set('Content-Security-Policy', self::csp());

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

    /**
     * Nilai header CSP. Saat dev server Vite aktif (file public/hot ada), origin-nya (http & ws untuk
     * HMR) ditambahkan; file itu tidak pernah ada di production (aset dari `npm run build`).
     */
    public static function csp(): string
    {
        $csp = self::CSP;
        $hot = Vite::hotFile();

        if (is_file($hot)) {
            $origin = rtrim(trim((string) file_get_contents($hot)), '/');

            foreach (self::DIRECTIVE_VITE as $directive) {
                $csp[$directive][] = $origin;
            }

            $csp['connect-src'][] = (string) preg_replace('#^http#', 'ws', $origin);
        }

        return implode('; ', array_map(
            fn (string $directive, array $sumber): string => $directive.' '.implode(' ', $sumber),
            array_keys($csp),
            $csp,
        ));
    }
}
