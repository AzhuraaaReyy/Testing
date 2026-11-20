<?php

use App\Enums\Role;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\RingkasanController;
use App\Http\Controllers\Api\SiswaController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

/*
| API v1 (Bearer token, Laravel Sanctum). Dokumentasi: docs/api/openapi.yaml.
| Aturan bisnis & validasi sama dengan panel Admin/portal (Actions & FormRequest yang sama).
*/

Route::prefix('v1')->name('api.')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Semua peran: akun sendiri, ringkasan, dan laporan (Guru/Siswa dibatasi Kelasnya, PRD 22.4).
    Route::middleware(['auth:sanctum', EnsureRole::for(...Role::cases())])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::get('/ringkasan', RingkasanController::class)->name('ringkasan');
        Route::get('/laporan/{jenis}', LaporanController::class)->name('laporan');
    });

    // Admin: CRUD + Trash Siswa, Kelas, Guru (PRD 5–7, 13).
    Route::middleware(['auth:sanctum', EnsureRole::for(Role::Admin)])->group(function (): void {
        $resources = [
            'siswa' => SiswaController::class,
            'kelas' => KelasController::class,
            'guru' => GuruController::class,
        ];

        foreach ($resources as $nama => $controller) {
            Route::apiResource($nama, $controller)->parameters([$nama => $nama]);
            Route::post("/{$nama}/{{$nama}}/pulihkan", [$controller, 'pulihkan'])->name("{$nama}.pulihkan");
        }
    });
});
