<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Membedakan "session berakhir" dari "belum pernah login" (PRD 4.6 poin 3).
 *
 * Saat login dipasang cookie penanda yang hidup sampai browser ditutup (lebih lama dari session
 * yang bisa kedaluwarsa karena idle), dan dihapus saat logout. Jika tamu membuka halaman
 * terlindungi sementara penanda masih ada, berarti session-nya berakhir, bukan logout.
 */
final class SesiLogin
{
    public const COOKIE = 'pernah_login';

    public const PESAN = 'Sesi Anda telah berakhir. Silakan login kembali.';

    /** Kunci flash pesan untuk halaman login portal (Blade). */
    public const FLASH = 'sesi_berakhir';

    public static function tandai(): void
    {
        // 0 menit = cookie sesi browser (hilang saat browser ditutup).
        Cookie::queue(self::COOKIE, '1', 0);
    }

    public static function lupakan(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }

    /**
     * Dipanggil ketika tamu ditolak masuk halaman terlindungi. Pesan hanya diumumkan pada
     * navigasi halaman penuh; permintaan JSON/Livewire (401/419) dimuat ulang oleh frontend
     * sehingga pesan muncul pada navigasi berikutnya.
     */
    public static function umumkanJikaBerakhir(Request $request): void
    {
        // ajax(): fetch fragmen portal (services/http.ts); X-Livewire: permintaan update Livewire.
        if (! $request->hasCookie(self::COOKIE) || $request->expectsJson() || $request->ajax() || $request->hasHeader('X-Livewire')) {
            return;
        }

        self::lupakan();

        if ($request->routeIs('filament.*')) {
            Notification::make()->warning()->title(self::PESAN)->persistent()->send();

            return;
        }

        $request->session()->flash(self::FLASH, self::PESAN);
    }
}
