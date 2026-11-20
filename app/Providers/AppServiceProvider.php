<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public const BATAS_API_PER_MENIT = 60;

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
        // Format angka mengikuti locale aplikasi: pemisah ribuan titik, mis. 1.050 (PRD 18.2).
        // Dipakai Number::format() Laravel, termasuk ringkasan pagination & angka di Filament.
        Number::useLocale($this->app->getLocale());

        // API: 60 permintaan/menit per akun (tamu: per IP). Login tetap dibatasi
        // 3 kegagalan/15 menit oleh AuthenticateUserAction (PRD 4.5).
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(self::BATAS_API_PER_MENIT)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }
}
