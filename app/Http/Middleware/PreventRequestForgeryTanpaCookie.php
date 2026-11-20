<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

/**
 * Proteksi CSRF bawaan Laravel tanpa cookie XSRF-TOKEN (SEC2-03).
 *
 * Cookie itu sengaja dapat dibaca JavaScript (tanpa HttpOnly) untuk library seperti axios yang
 * mengirim header X-XSRF-TOKEN. Aplikasi ini tidak memakainya: form Blade mengirim field _token,
 * Livewire/Filament membaca token dari atribut data-csrf, dan fetch portal hanya GET.
 * Validasi token tetap berjalan seperti biasa; hanya cookie yang tidak lagi dikirim.
 *
 * Dipasang lewat binding container (AppServiceProvider), sehingga grup "web", panel Filament,
 * dan Livewire tetap merujuk nama class PreventRequestForgery yang sama.
 */
final class PreventRequestForgeryTanpaCookie extends PreventRequestForgery
{
    /** @var bool */
    protected $addHttpCookie = false;
}
