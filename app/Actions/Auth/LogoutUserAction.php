<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Mengakhiri session sepenuhnya (PRD 4.6–4.7): logout, session lama dibuang, token CSRF diganti.
 * Dipakai logout portal, logout panel Admin (aksi Livewire), dan EnsureRole (profil masuk Trash).
 */
final class LogoutUserAction
{
    public const BERHASIL = 'Anda berhasil keluar dari sistem.';

    public function handle(): void
    {
        Auth::logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
