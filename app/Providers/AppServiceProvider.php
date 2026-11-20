<?php

namespace App\Providers;

use App\Http\Middleware\PreventRequestForgeryTanpaCookie;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public const BATAS_API_PER_MENIT = 60;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // CSRF tanpa cookie XSRF-TOKEN yang dapat dibaca JavaScript (SEC2-03).
        $this->app->bind(PreventRequestForgery::class, PreventRequestForgeryTanpaCookie::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Format angka mengikuti locale aplikasi: pemisah ribuan titik, mis. 1.050 (PRD 18.2).
        // Dipakai Number::format() Laravel, termasuk ringkasan pagination & angka di Filament.
        Number::useLocale($this->app->getLocale());

        // API: 60 permintaan/menit per akun (tamu: per IP). Login tetap dibatasi
        // 3 kegagalan/15 menit oleh AuthenticateUserAction (PRD 4.5).
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(self::BATAS_API_PER_MENIT)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));

        // Aturan password akun baru/diubah (Admin & portal), SEC-05: min. 8, memuat huruf dan angka.
        // Di production juga ditolak bila pernah bocor (Have I Been Pwned; hanya 5 karakter awal
        // hash SHA-1 yang dikirim, bukan password-nya; bila layanan tak terjangkau, dianggap lolos).
        Password::defaults(fn (): Password => App::isProduction()
            ? Password::min(8)->letters()->numbers()->uncompromised()
            : Password::min(8)->letters()->numbers());
    }
}
