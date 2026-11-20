<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Token API yang kedaluwarsa (config sanctum.expiration) memang ditolak, tetapi tetap tersimpan.
// Dihapus tiap hari bila sudah kedaluwarsa lebih dari 24 jam (SEC-07).
// Produksi wajib menjalankan scheduler: cron "* * * * * php artisan schedule:run".
Schedule::command('sanctum:prune-expired', ['--hours' => 24])->daily()->onOneServer()->withoutOverlapping();
