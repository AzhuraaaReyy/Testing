<?php

namespace App\Actions\Auth;

use App\Enums\Role;
use App\Models\User;
use App\Support\SesiLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login satu pintu untuk panel Admin (Filament) dan portal Guru/Siswa (Blade).
 * - Pesan gagal bersifat umum, tidak membedakan email/password (PRD 4.4).
 * - Maksimal 3 kegagalan, lalu dibatasi 15 menit; reset setelah berhasil (PRD 4.5).
 * - Akun dengan peran di luar $peran ditolak, begitu pula akun yang profilnya
 *   sudah di Trash (PRD 22.2).
 */
final class AuthenticateUserAction
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 15 * 60;

    public const GAGAL = 'Email atau password yang Anda masukkan salah.';

    /**
     * @throws ValidationException Pesan pada kunci "email".
     */
    public function handle(string $email, string $password, string $ip, Role ...$peran): User
    {
        $key = $this->throttleKey($email, $ip);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $menit = (int) ceil(RateLimiter::availableIn($key) / 60);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$menit} menit.",
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password) || ! in_array($user->role, $peran, true) || ! $user->isActive()) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => self::GAGAL]);
        }

        RateLimiter::clear($key);
        Auth::login($user);
        SesiLogin::tandai();

        return $user;
    }

    private function throttleKey(string $email, string $ip): string
    {
        return 'login:'.Str::transliterate(Str::lower($email)).'|'.$ip;
    }
}
