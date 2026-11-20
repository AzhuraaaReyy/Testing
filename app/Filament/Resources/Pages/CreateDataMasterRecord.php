<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\BatalDenganKonfirmasi;
use App\Filament\Resources\Pages\Concerns\SubjudulDataMaster;
use Filament\Resources\Pages\CreateRecord;

/**
 * Halaman Tambah bersama untuk CRUD Siswa, Kelas, dan Guru.
 */
abstract class CreateDataMasterRecord extends CreateRecord
{
    use BatalDenganKonfirmasi;
    use SubjudulDataMaster;

    protected function jenisHalaman(): string
    {
        return 'create';
    }
}
