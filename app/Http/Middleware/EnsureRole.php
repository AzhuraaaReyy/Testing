<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi rute untuk peran tertentu. Pemakaian: ->middleware(EnsureRole::for(Role::Guru, Role::Siswa)).
 * - Peran tidak sesuai → diarahkan ke beranda perannya sendiri.
 * - Profil Guru/Siswa sudah di Trash → session diakhiri dan diarahkan ke Login.
 */
final class EnsureRole
{
    public static function for(Role ...$roles): string
    {
        return self::class.':'.implode(',', array_map(fn (Role $role): string => $role->value, $roles));
    }

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($user->role->loginRoute());
        }

        if (! in_array($user->role->value, $roles, true)) {
            return redirect()->route($user->role->homeRoute());
        }

        return $next($request);
    }
}
