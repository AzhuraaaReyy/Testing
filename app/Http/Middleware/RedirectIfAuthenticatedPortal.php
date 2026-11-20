<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pengganti middleware "guest" untuk Login portal (/login).
 *
 * Satu browser memegang satu session, sehingga:
 * - Guru/Siswa yang sudah login diarahkan ke berandanya.
 * - Admin yang sudah login TIDAK dilempar ke panel Admin; halaman login portal menjelaskan bahwa
 *   ia perlu logout dulu (pages.auth.login). Kiriman form login (mis. dari tab lama) tidak
 *   diproses dan dikembalikan ke halaman tersebut, agar session Admin tidak tertimpa diam-diam.
 */
final class RedirectIfAuthenticatedPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        if ($role === null) {
            return $next($request);
        }

        if ($role !== Role::Admin) {
            return redirect()->route($role->homeRoute());
        }

        return $request->isMethod('GET') ? $next($request) : redirect()->route('login');
    }
}
