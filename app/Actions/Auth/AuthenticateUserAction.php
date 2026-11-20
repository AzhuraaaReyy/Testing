<?php

namespace App\Actions\Auth;

use App\Enums\PintuLogin;
use App\Models\User;
use App\Support\SesiLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login untuk ketiga pintu: panel Admin (Filament), portal Guru/Siswa (Blade), dan API (token).
 * - Pesan gagal bersifat umum, tidak membedakan email/password (PRD 4.4).
 * - Maksimal 3 kegagalan per pintu, lalu dibatasi 15 menit; reset setelah berhasil (PRD 4.5).
 *   Penghitung dipisah per pintu agar mencoba di pintu yang salah tidak mengunci pintu yang benar.
 * - Akun dengan peran di luar pintunya ditolak, begitu pula akun yang profilnya di Trash (PRD 22.2).
 */
final class AuthenticateUserAction
{
    public const MAX_ATTEMPTS = 3;

    public const DECAY_SECONDS = 15 * 60;

    public const GAGAL = 'Email atau password yang Anda masukkan salah.';

    /**
     * Login berbasis session (panel Admin & portal).
     *
     * @throws ValidationException Pesan pada kunci "email".
     */
    public function handle(string $email, string $password, string $ip, PintuLogin $pintu): User
    {
        $user = $this->verifikasi($email, $password, $ip, $pintu);

        Auth::login($user);
        SesiLogin::tandai();
        $this->saringTujuan($user);

        return $user;
    }

    /**
     * Pengecekan kredensial tanpa membuat session; dipakai juga login API (token).
     *
     * @throws ValidationException Pesan pada kunci "email".
     */
    public function verifikasi(string $email, string $password, string $ip, PintuLogin $pintu): User
    {
        $key = self::throttleKey($email, $ip, $pintu);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $menit = (int) ceil(RateLimiter::availableIn($key) / 60);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$menit} menit.",
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password) || ! in_array($user->role, $pintu->peran(), true) || ! $user->isActive()) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => self::GAGAL]);
        }

        RateLimiter::clear($key);

        return $user;
    }

    public static function throttleKey(string $email, string $ip, PintuLogin $pintu): string
    {
        return "login:{$pintu->value}:".Str::transliterate(Str::lower($email)).'|'.$ip;
    }

    /**
     * Halaman yang tadinya diminta (url.intended) hanya dipakai bila berada di wilayah peran
     * pengguna; selain itu pengguna diarahkan ke berandanya (PRD 4.6 poin 4: "masih valid dan
     * dapat diakses"). Mencegah siswa mendarat di halaman Admin (403) atau sebaliknya.
     */
    private function saringTujuan(User $user): void
    {
        $tujuan = Session::get('url.intended');

        if (! is_string($tujuan)) {
            return;
        }

        $path = trim((string) parse_url($tujuan, PHP_URL_PATH), '/');
        $wilayah = $user->role->wilayahUrl();

        if ($path !== $wilayah && ! str_starts_with($path, "{$wilayah}/")) {
            Session::forget('url.intended');
        }
    }
}
