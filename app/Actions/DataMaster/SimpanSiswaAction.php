<?php

namespace App\Actions\DataMaster;

use App\Models\Siswa;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Tambah/edit Siswa beserta akun portalnya (PRD 5.4, 5.6, 22.2), dalam satu transaksi.
 * Data sudah divalidasi SiswaRequest. Dipakai Filament & API.
 */
final class SimpanSiswaAction
{
    public function __construct(private readonly SinkronAkunPortalAction $sinkronAkun) {}

    /**
     * @param  array<string, mixed>  $data  Kunci "akun" opsional: tanpa kunci = akun tidak diubah.
     */
    public function handle(array $data, ?Siswa $siswa = null): Siswa
    {
        return DB::transaction(function () use ($data, $siswa): Siswa {
            $siswa ??= new Siswa;
            $siswa->fill(Arr::only($data, ['nis', 'nama', 'jenis_kelamin', 'alamat', 'kelas_id']))->save();

            if (is_array($data['akun'] ?? null)) {
                $this->sinkronAkun->handle($siswa, $data['akun']);
            }

            return $siswa;
        });
    }
}
