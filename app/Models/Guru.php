<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use App\Enums\Role;
use Database\Factories\GuruFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string|null $nip
 * @property string $nama
 * @property JenisKelamin $jenis_kelamin
 * @property string $alamat
 * @property string $no_telepon
 */
#[Table('guru')]
#[Fillable(['nip', 'nama', 'jenis_kelamin', 'alamat', 'no_telepon'])]
class Guru extends Model
{
    /** @use HasFactory<GuruFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Masuk Trash selalu melepas seluruh relasi guru_kelas (PRD 7.6, 12.4).
     */
    protected static function booted(): void
    {
        static::deleting(function (Guru $guru): void {
            $guru->kelas()->detach();
        });
    }

    protected function casts(): array
    {
        return [
            'jenis_kelamin' => JenisKelamin::class,
        ];
    }

    /**
     * Kelas yang diajar, selalu dalam urutan baku Tingkat → Jurusan → Nama Kelas (PRD 7.2).
     *
     * @return BelongsToMany<Kelas, $this>
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')->withTimestamps()->urutanTampilan();
    }

    /**
     * Akun portal (opsional) milik Guru ini; dibuat Admin lewat form CRUD (PRD 22.2).
     *
     * @return HasOne<User, $this>
     */
    public function akun(): HasOne
    {
        return $this->hasOne(User::class)->withAttributes(['role' => Role::Guru]);
    }

    /**
     * Pulihkan dari Trash sekaligus menghubungkan ke minimal satu Kelas aktif, dalam satu
     * transaksi sehingga Guru tidak pernah aktif tanpa Kelas (PRD 7.6, 21.2).
     *
     * @param  list<int>  $kelasIds
     */
    public function pulihkanKeKelas(array $kelasIds): bool
    {
        return DB::transaction(function () use ($kelasIds): bool {
            $this->kelas()->sync($kelasIds);

            return (bool) $this->restore();
        });
    }
}
