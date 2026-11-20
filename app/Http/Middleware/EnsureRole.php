<?php

namespace App\Http\Middleware;

use App\Actions\Auth\LogoutUserAction;
use App\Enums\Role;
use App\Models\User;
use App\Support\LogKeamanan;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi rute untuk peran tertentu. Pemakaian: ->middleware(EnsureRole::for(Role::Guru, Role::Siswa)).
 *
 * Web (session):
 * - Peran tidak sesuai → diarahkan ke beranda perannya sendiri.
 * - Profil Guru/Siswa sudah di Trash → session diakhiri dan diarahkan ke Login.
 *
 * API (token, /api/*):
 * - Peran tidak sesuai → 403.
 * - Profil Guru/Siswa sudah di Trash → token dicabut, 401.
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
        $api = $request->is('api/*');

        if (! $user->isActive()) {
            LogKeamanan::catat('akun.nonaktif.dikeluarkan', ['user_id' => $user->id, 'peran' => $user->role->value, 'ip' => $request->ip()], 'info');

            if ($api) {
                $token = $user->currentAccessToken();

                if ($token instanceof PersonalAccessToken) {
                    $token->delete();
                }

                throw new AuthenticationException;
            }

            app(LogoutUserAction::class)->handle();

            return redirect()->route($user->role->loginRoute());
        }

        if (! in_array($user->role->value, $roles, true)) {
            LogKeamanan::catat('akses.ditolak', [
                'user_id' => $user->id,
                'peran' => $user->role->value,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            abort_if($api, 403);

            return redirect()->route($user->role->homeRoute());
        }

        return $next($request);
    }
}
