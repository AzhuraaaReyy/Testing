<?php

namespace App\Filament\Forms\Components;

use App\Models\Kelas;
use Filament\Forms\Components\Select;

/**
 * Pilihan Kelas untuk form Siswa/Guru dan dialog pemulihan dari Trash (PRD 5.2, 7.2, 13.2):
 * searchable, hanya Kelas aktif (validasi "in" bawaan Select menolak Kelas di Trash),
 * dan terurut Tingkat → Jurusan → Nama Kelas.
 */
class KelasAktifSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Kelas')
            ->required()
            ->searchable()
            ->preload()
            ->options(fn (): array => Kelas::query()->urutanTampilan()->pluck('nama_kelas', 'id')->all());
    }
}
