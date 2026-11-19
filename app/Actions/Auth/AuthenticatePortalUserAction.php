<?php

namespace App\Actions\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login portal Guru/Siswa.
 * - Pesan gagal bersifat umum, tidak membedakan email/password (PRD 4.4).
 * - Maksimal 3 kegagalan, lalu dibatasi 15 menit; reset setelah berhasil (PRD 4.5).
 * - Akun Admin ditolak di sini (login Admin lewat panel Filament), begitu pula akun
 *   yang profilnya sudah di Trash.
 */
final class AuthenticatePortalUserAction
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 15 * 60;

    public const GAGAL = 'Email atau password yang Anda masukkan salah.';

    /**
     * @throws ValidationException
     */
    public function handle(string $email, string $password, string $ip): User
    {
        $key = $this->throttleKey($email, $ip);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $menit = (int) ceil(RateLimiter::availableIn($key) / 60);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$menit} menit.",
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password) || $user->role === Role::Admin || ! $user->isActive()) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => self::GAGAL]);
        }

        RateLimiter::clear($key);
        Auth::login($user);

        return $user;
    }

    private function throttleKey(string $email, string $ip): string
    {
        return 'portal-login:'.Str::transliterate(Str::lower($email)).'|'.$ip;
    }
}
