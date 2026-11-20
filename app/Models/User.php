<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Role $role
 * @property int|null $siswa_id
 * @property int|null $guru_id
 * @property-read string $inisial
 */
// role, siswa_id, guru_id sengaja tidak fillable: hanya diisi eksplisit oleh Admin/seeder
// (forceFill) agar peran tidak dapat dinaikkan lewat mass assignment.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Panel Filament (CRUD) hanya untuk Admin. Tanpa ini, semua user yang login
     * dapat membuka panel ketika APP_ENV=local.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === Role::Admin;
    }

    /**
     * @return BelongsTo<Siswa, $this>
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * @return BelongsTo<Guru, $this>
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Akun Guru/Siswa hanya aktif selama profilnya aktif (tidak di Trash);
     * Siswa juga wajib masih memiliki Kelas (PRD 12.1).
     */
    public function isActive(): bool
    {
        return match ($this->role) {
            Role::Admin => true,
            Role::Siswa => $this->siswa?->kelas_id !== null,
            Role::Guru => $this->guru !== null,
        };
    }

    /**
     * Inisial (maks. dua huruf) untuk avatar Navbar, mis. "Admin Sekolah" → "AS".
     *
     * @return Attribute<string, never>
     */
    protected function inisial(): Attribute
    {
        return Attribute::get(fn (): string => Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $kata): string => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->implode(''));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }
}
