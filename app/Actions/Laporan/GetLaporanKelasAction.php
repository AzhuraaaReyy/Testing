<?php

namespace App\Actions\Laporan;

use App\DTOs\Laporan\KelasRingkasanData;
use App\Enums\JenisLaporan;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Mengambil data tiga halaman laporan. Pagination berbasis Kelas (PRD 15.5), sehingga
 * daftar Siswa/Guru satu Kelas tidak pernah terpisah ke halaman lain.
 * Data di Trash tersaring otomatis oleh SoftDeletes pada Kelas, Siswa, dan Guru.
 */
final class GetLaporanKelasAction
{
    public const PER_PAGE = 10;

    /**
     * @param  list<int>|null  $kelasIds  Batasi ke Kelas tertentu (portal Guru/Siswa); null = seluruh Kelas.
     * @return LengthAwarePaginator<int, KelasRingkasanData>
     */
    public function handle(JenisLaporan $jenis, int $page = 1, ?array $kelasIds = null): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Kelas> $paginator */
        $paginator = Kelas::query()
            ->select(['id', 'nama_kelas', 'tingkat', 'jurusan'])
            ->when($kelasIds !== null, fn ($query) => $query->whereKey($kelasIds))
            ->with($this->relasi($jenis))
            ->urutanTampilan()
            ->paginate(perPage: self::PER_PAGE, page: $page);

        return $paginator->through(fn (Kelas $kelas): KelasRingkasanData => KelasRingkasanData::fromModel($kelas));
    }

    /**
     * Eager load hanya relasi yang ditampilkan (cegah N+1), dan hanya kolom yang dibutuhkan.
     *
     * @return array<string, \Closure(Relation<*, *, *>): void>
     */
    private function relasi(JenisLaporan $jenis): array
    {
        $relasi = [];

        if ($jenis->menampilkanSiswa()) {
            $relasi['siswa'] = fn (Relation $query) => $query->getQuery()->select(['siswa.id', 'siswa.nama', 'siswa.kelas_id']);
        }

        if ($jenis->menampilkanGuru()) {
            $relasi['guru'] = fn (Relation $query) => $query->getQuery()->select(['guru.id', 'guru.nama']);
        }

        return $relasi;
    }
}
