<?php

use App\Enums\Role;
use App\Http\Controllers\Auth\PortalLoginController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\PreventSensitiveCaching;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Beranda: tamu ke Login portal; pengguna login ke beranda perannya.
Route::get('/', fn (Request $request) => redirect()->route($request->user()?->role->homeRoute() ?? 'login'));

/*
| Login portal Guru/Siswa (Blade). Login Admin dilayani panel Filament di /admin/login.
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [PortalLoginController::class, 'create'])->name('login');
    Route::post('/login', [PortalLoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [PortalLoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
| Seluruh halaman Admin (Dashboard, CRUD, Trash, Laporan) dilayani panel Filament di /admin.
|
| Portal Guru/Siswa: read-only, tanpa CRUD (PRD v1.3).
| {jenis} di-bind otomatis ke enum App\Enums\JenisLaporan; nilai lain menghasilkan 404.
| auth.session: session lain milik akun ini berakhir begitu password-nya diganti Admin (SEC2-01),
| sama seperti AuthenticateSession di panel Admin.
*/
Route::middleware(['auth', 'auth.session', EnsureRole::for(Role::Guru, Role::Siswa), PreventSensitiveCaching::class])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::get('/', PortalDashboardController::class)->name('dashboard');
        Route::get('/laporan/{jenis}', LaporanController::class)->name('laporan.show');
    });
