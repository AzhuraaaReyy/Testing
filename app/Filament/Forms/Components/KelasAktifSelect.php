<?php

namespace App\Filament\Forms\Components;

use App\Models\Kelas;
use Filament\Forms\Components\Select;

/**
 * Pilihan Kelas untuk form Siswa/Guru dan dialog pemulihan dari Trash (PRD 5.2, 7.2, 13.2):
 * searchable, hanya Kelas aktif, terurut Tingkat → Jurusan → Nama Kelas.
 * Aturan validasi (wajib, Kelas aktif) berasal dari SiswaRequest/GuruRequest via AturanFormRequest.
 */
class KelasAktifSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Kelas')
            ->searchable()
            ->preload()
            ->options(fn (): array => Kelas::query()->urutanTampilan()->pluck('nama_kelas', 'id')->all());
    }
}
