<?php

namespace App\Actions\DataMaster;

use App\Models\Kelas;
use Illuminate\Support\Arr;

/**
 * Tambah/edit Kelas (PRD 6.4). Data sudah divalidasi KelasRequest. Dipakai Filament & API.
 */
final class SimpanKelasAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?Kelas $kelas = null): Kelas
    {
        $kelas ??= new Kelas;
        $kelas->fill(Arr::only($data, ['nama_kelas', 'tingkat', 'jurusan', 'keterangan']))->save();

        return $kelas;
    }
}
