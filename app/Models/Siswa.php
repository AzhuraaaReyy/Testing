<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use App\Enums\Role;
use Database\Factories\SiswaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nis
 * @property string $nama
 * @property JenisKelamin $jenis_kelamin
 * @property string $alamat
 * @property int|null $kelas_id
 */
#[Table('siswa')]
#[Fillable(['nis', 'nama', 'jenis_kelamin', 'alamat', 'kelas_id'])]
class Siswa extends Model
{
    /** @use HasFactory<SiswaFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Masuk Trash selalu melepas relasi Kelas (PRD 5.7, 12.4), dari jalur mana pun data dihapus.
     */
    protected static function booted(): void
    {
        static::deleting(function (Siswa $siswa): void {
            if (! $siswa->isForceDeleting()) {
                $siswa->forceFill(['kelas_id' => null])->saveQuietly();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'jenis_kelamin' => JenisKelamin::class,
        ];
    }

    /**
     * @return BelongsTo<Kelas, $this>
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Akun portal (opsional) milik Siswa ini; dibuat Admin lewat form CRUD (PRD 22.2).
     *
     * @return HasOne<User, $this>
     */
    public function akun(): HasOne
    {
        return $this->hasOne(User::class)->withAttributes(['role' => Role::Siswa]);
    }

    /**
     * Pulihkan dari Trash sekaligus menempatkan ke satu Kelas aktif, sehingga Siswa
     * tidak pernah aktif tanpa Kelas (PRD 5.7, 21.1). Satu query UPDATE.
     */
    public function pulihkanKeKelas(int $kelasId): bool
    {
        $this->kelas_id = $kelasId;

        return (bool) $this->restore();
    }
}
