<?php

namespace App\Providers;

use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

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
        // Format angka mengikuti locale aplikasi: pemisah ribuan titik, mis. 1.050 (PRD 18.2).
        // Dipakai Number::format() Laravel, termasuk ringkasan pagination & angka di Filament.
        Number::useLocale($this->app->getLocale());
    }
}
