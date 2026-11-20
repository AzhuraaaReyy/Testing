<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk semua respons (portal, panel Admin, API) — SEC-03:
 * - Content-Security-Policy per area (SEC2-02):
 *   - portal (login, /portal, halaman error): ketat, script & style hanya dari origin sendiri atau
 *     bernonce (Vite::useCspNonce: tag @vite, @fonts, dan script inline layout);
 *   - panel Admin (/admin): longgar secara sengaja, lihat ADMIN di bawah;
 *   - API (/api): tidak memuat apa pun, karena responsnya JSON.
 * - browser tidak menebak tipe konten (MIME sniffing);
 * - URL lengkap tidak bocor ke situs lain lewat header Referer;
 * - fitur perangkat (kamera, mikrofon, lokasi, dll.) dinonaktifkan karena tidak dipakai;
 * - versi PHP (X-Powered-By) tidak diumumkan;
 * - HSTS hanya di produksi lewat HTTPS, agar pengembangan lokal via HTTP tidak terganggu.
 */
final class SecurityHeaders
{
    public const HSTS = 'max-age=31536000; includeSubDomains';

    public const AREA_PORTAL = 'portal';

    public const AREA_ADMIN = 'admin';

    public const AREA_API = 'api';

    /** Placeholder nonce di PORTAL; diganti nonce acak per request. */
    private const NONCE = '{nonce}';

    /** Directive bersama portal & Admin: semua sumber hanya dari origin sendiri. */
    private const DASAR = [
        'default-src' => ["'self'"],
        'img-src' => ["'self'", 'data:'],
        'font-src' => ["'self'", 'data:'],
        'connect-src' => ["'self'"],
        'media-src' => ["'self'"],
        'object-src' => ["'none'"],
        'worker-src' => ["'self'"],
        'manifest-src' => ["'self'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
        'frame-ancestors' => ["'none'"],
    ];

    /** @var array<string, list<string>> */
    public const PORTAL = [
        ...self::DASAR,
        'script-src' => ["'self'", "'nonce-".self::NONCE."'"],
        'style-src' => ["'self'", "'nonce-".self::NONCE."'"],
        'frame-src' => ["'none'"],
    ];

    /**
     * Pengecualian yang disengaja (by-design) khusus panel Admin:
     * - script-src 'unsafe-inline' 'unsafe-eval': Livewire & Filament menyisipkan <script> inline,
     *   dan Alpine.js (bawaan Livewire) mengevaluasi ekspresi x-data/x-on lewat new Function().
     *   Tanpa nonce: bila nonce ada, browser mengabaikan 'unsafe-inline' dan script Filament mati.
     * - style-src 'unsafe-inline': atribut style="" dan <style> inline dari Filament/Livewire.
     * XSS tetap dicegah oleh escaping Blade ({{ }}) di seluruh view. Avatar akun dibuat lokal
     * (InisialAvatarProvider, data: URI), jadi tidak ada domain pihak ketiga yang diizinkan.
     *
     * @var array<string, list<string>>
     */
    public const ADMIN = [
        ...self::DASAR,
        'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'frame-src' => ["'self'"],
    ];

    /** @var array<string, list<string>> */
    public const API = [
        'default-src' => ["'none'"],
        'base-uri' => ["'none'"],
        'form-action' => ["'none'"],
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
        $area = self::area($request);

        // Nonce harus ada sebelum view dirender: dipakai @vite, @fonts, dan script inline layout.
        if ($area === self::AREA_PORTAL) {
            Vite::useCspNonce();
        }

        $response = $next($request);

        foreach (self::HEADERS as $nama => $nilai) {
            $response->headers->set($nama, $nilai);
        }

        // Halaman error debug Laravel (hanya APP_DEBUG=true) memakai <style>/<script> inline tanpa
        // nonce; di production halaman error memakai layout portal yang bernonce.
        if ($area === self::AREA_PORTAL && config('app.debug') && $response->getStatusCode() >= 500) {
            $area = self::AREA_ADMIN;
        }

        $response->headers->set('Content-Security-Policy', self::csp($area, Vite::cspNonce()));

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

    public static function area(Request $request): string
    {
        return match (true) {
            $request->is('api', 'api/*') => self::AREA_API,
            $request->is('admin', 'admin/*') => self::AREA_ADMIN,
            default => self::AREA_PORTAL,
        };
    }

    /**
     * Nilai header CSP untuk satu area. Saat dev server Vite aktif (file public/hot ada), origin-nya
     * (http & ws untuk HMR) ditambahkan; file itu tidak pernah ada di production.
     */
    public static function csp(string $area = self::AREA_PORTAL, ?string $nonce = null): string
    {
        $csp = match ($area) {
            self::AREA_ADMIN => self::ADMIN,
            self::AREA_API => self::API,
            default => self::PORTAL,
        };

        $hot = Vite::hotFile();

        if ($area !== self::AREA_API && is_file($hot)) {
            $origin = rtrim(trim((string) file_get_contents($hot)), '/');

            foreach (self::DIRECTIVE_VITE as $directive) {
                $csp[$directive][] = $origin;
            }

            $csp['connect-src'][] = (string) preg_replace('#^http#', 'ws', $origin);

            // HMR Vite menyuntikkan CSS lewat <style> tanpa nonce; hanya selama `npm run dev`.
            if ($area === self::AREA_PORTAL) {
                $csp['style-src'] = ["'self'", "'unsafe-inline'", $origin];
            }
        }

        $hasil = implode('; ', array_map(
            fn (string $directive, array $sumber): string => $directive.' '.implode(' ', $sumber),
            array_keys($csp),
            $csp,
        ));

        return str_replace(self::NONCE, (string) $nonce, $hasil);
    }
}
