<?php

namespace App\Actions\DataMaster;

use App\Models\Guru;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Tambah/edit Guru beserta Kelas yang diajar dan akun portalnya (PRD 7.4, 22.2), dalam satu
 * transaksi. Data sudah divalidasi GuruRequest. Dipakai Filament & API.
 */
final class SimpanGuruAction
{
    public function __construct(private readonly SinkronAkunPortalAction $sinkronAkun) {}

    /**
     * @param  array<string, mixed>  $data  Kunci "kelas" & "akun" opsional: tanpa kunci = tidak diubah.
     */
    public function handle(array $data, ?Guru $guru = null): Guru
    {
        return DB::transaction(function () use ($data, $guru): Guru {
            $atribut = Arr::only($data, ['nip', 'nama', 'jenis_kelamin', 'alamat', 'no_telepon']);

            // NIP kosong disimpan NULL agar index unik tidak bentrok antar-Guru tanpa NIP.
            if (array_key_exists('nip', $atribut) && blank($atribut['nip'])) {
                $atribut['nip'] = null;
            }

            $guru ??= new Guru;
            $guru->fill($atribut)->save();

            if (is_array($data['kelas'] ?? null)) {
                $guru->kelas()->sync(array_map(intval(...), array_values($data['kelas'])));
            }

            if (is_array($data['akun'] ?? null)) {
                $this->sinkronAkun->handle($guru, $data['akun']);
            }

            return $guru;
        });
    }
}
