<?php

namespace App\Models;

use App\Enums\Tingkat;
use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $nama_kelas
 * @property Tingkat $tingkat
 * @property string|null $jurusan
 * @property string|null $keterangan
 */
#[Table('kelas')]
#[Fillable(['nama_kelas', 'tingkat', 'jurusan', 'keterangan'])]
class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Penjaga terakhir integritas relasi: Kelas yang masih dipakai tidak dapat masuk Trash.
     */
    protected static function booted(): void
    {
        static::deleting(fn (Kelas $kelas): bool => $kelas->alasanTidakDapatDihapus() === null);
    }

    /**
     * Alasan Kelas tidak dapat dihapus, atau null bila boleh. Hanya Siswa/Guru aktif yang
     * dihitung; relasi dengan data di Trash tidak menghalangi (PRD 6.6, 21.3).
     */
    public function alasanTidakDapatDihapus(): ?string
    {
        return match (true) {
            $this->siswa()->exists() => 'Kelas masih memiliki Siswa aktif.',
            $this->guru()->exists() => 'Kelas masih memiliki Guru aktif yang terhubung.',
            default => null,
        };
    }

    protected function casts(): array
    {
        return [
            'tingkat' => Tingkat::class,
        ];
    }

    /**
     * Siswa aktif di Kelas ini (Siswa di Trash otomatis tersaring oleh SoftDeletes).
     *
     * @return HasMany<Siswa, $this>
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    /**
     * Guru aktif yang mengajar Kelas ini.
     *
     * @return BelongsToMany<Guru, $this>
     */
    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')->withTimestamps();
    }

    /**
     * Urutan baku Kelas di seluruh sistem (PRD 15.6): Tingkat X → XI → XII, Jurusan A–Z, Nama Kelas A–Z.
     *
     * @param  Builder<Kelas>  $query
     */
    #[Scope]
    protected function urutanTampilan(Builder $query): void
    {
        $query
            ->orderByRaw(Tingkat::orderBySql($query->qualifyColumn('tingkat')))
            ->orderBy($query->qualifyColumn('jurusan'))
            ->orderBy($query->qualifyColumn('nama_kelas'));
    }
}
