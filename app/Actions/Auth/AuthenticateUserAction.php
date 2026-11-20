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
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;

/**
 * Login untuk ketiga pintu: panel Admin (Filament), portal Guru/Siswa (Blade), dan API (token).
 * - Pesan gagal bersifat umum, tidak membedakan email/password (PRD 4.4).
 * - Maksimal 3 kegagalan per pintu, lalu dibatasi 15 menit; reset setelah berhasil (PRD 4.5).
 *   Penghitung dipisah per pintu agar mencoba di pintu yang salah tidak mengunci pintu yang benar.
 * - Ditambah batas per IP lintas pintu & email (MAX_ATTEMPTS_PER_IP) melawan password spraying.
 * - Login gagal selalu berdurasi minimum yang sama (Timebox) agar email terdaftar tidak bisa ditebak.
 * - Akun dengan peran di luar pintunya ditolak, begitu pula akun yang profilnya di Trash (PRD 22.2).
 */
final class AuthenticateUserAction
{
    public const MAX_ATTEMPTS = 3;

    /**
     * Batas kegagalan per IP untuk semua pintu dan email sekaligus (SEC-01, anti password spraying).
     * Cukup longgar untuk banyak pengguna di balik satu IP sekolah (NAT).
     */
    public const MAX_ATTEMPTS_PER_IP = 20;

    public const DECAY_SECONDS = 15 * 60;

    /**
     * Durasi minimum login gagal (SEC-02). Di atas biaya bcrypt produksi (±215 ms, rounds 12),
     * sehingga email terdaftar dan tidak terdaftar sama lamanya.
     */
    public const TIMEBOX_MIKRODETIK = 300_000;

    public const GAGAL = 'Email atau password yang Anda masukkan salah.';

    public function __construct(private readonly Timebox $timebox) {}

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
        $ipKey = self::throttleKeyIp($ip);

        $this->tolakJikaTerkunci($ipKey, self::MAX_ATTEMPTS_PER_IP);
        $this->tolakJikaTerkunci($key, self::MAX_ATTEMPTS);

        // Kegagalan selalu memakan waktu minimum yang sama, terdaftar atau tidak (SEC-02).
        $user = $this->timebox->dontReturnEarly()->call(function (Timebox $timebox) use ($email, $password, $pintu): ?User {
            $user = $this->cocokkan($email, $password);

            if (! $user || ! in_array($user->role, $pintu->peran(), true) || ! $user->isActive()) {
                return null;
            }

            $timebox->returnEarly();

            return $user;
        }, self::TIMEBOX_MIKRODETIK);

        if (! $user) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            RateLimiter::hit($ipKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages(['email' => self::GAGAL]);
        }

        // Hanya penghitung per email yang direset. Penghitung per IP sengaja tidak direset agar
        // penyerang tidak bisa "mencuci" kegagalannya dengan sesekali login memakai akunnya sendiri.
        RateLimiter::clear($key);

        return $user;
    }

    /**
     * Email tidak terdaftar tetap menjalankan satu operasi hash dengan biaya yang sama seperti
     * pengecekan password, agar waktu respons tidak membocorkan email mana yang terdaftar.
     */
    private function cocokkan(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            Hash::make($password);

            return null;
        }

        return Hash::check($password, $user->password) ? $user : null;
    }

    public static function throttleKey(string $email, string $ip, PintuLogin $pintu): string
    {
        return "login:{$pintu->value}:".Str::transliterate(Str::lower($email)).'|'.$ip;
    }

    /** Penghitung kegagalan per IP, gabungan semua pintu dan semua email (anti password spraying). */
    public static function throttleKeyIp(string $ip): string
    {
        return "login-ip:{$ip}";
    }

    /** @throws ValidationException */
    private function tolakJikaTerkunci(string $key, int $maks): void
    {
        if (! RateLimiter::tooManyAttempts($key, $maks)) {
            return;
        }

        $menit = (int) ceil(RateLimiter::availableIn($key) / 60);

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$menit} menit.",
        ]);
    }

    /**
     * Halaman yang tadinya diminta (url.intended) hanya dipakai bila berada di wilayah peran
     * pengguna; selain itu pengguna diarahkan ke berandanya (PRD 4.6 poin 4: "masih valid dan
     * dapat diakses"). Mencegah siswa mendarat di halaman Admin (403) atau sebaliknya, serta
     * redirect ke domain lain (open redirect).
     */
    private function saringTujuan(User $user): void
    {
        $tujuan = Session::get('url.intended');

        if (! is_string($tujuan)) {
            return;
        }

        $path = trim((string) parse_url($tujuan, PHP_URL_PATH), '/');
        $host = parse_url($tujuan, PHP_URL_HOST);
        $wilayah = $user->role->wilayahUrl();

        // Tujuan harus di host yang sama dengan permintaan login ini (SEC-06), bukan domain lain.
        $hostAsing = is_string($host) && strcasecmp($host, request()->getHost()) !== 0;

        if ($hostAsing || ($path !== $wilayah && ! str_starts_with($path, "{$wilayah}/"))) {
            Session::forget('url.intended');
        }
    }
}
