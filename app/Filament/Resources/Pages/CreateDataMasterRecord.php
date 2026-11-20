<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\BatalDenganKonfirmasi;
use App\Filament\Resources\Pages\Concerns\HalamanDataMaster;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Halaman Tambah bersama untuk CRUD Siswa, Kelas, dan Guru.
 */
abstract class CreateDataMasterRecord extends CreateRecord
{
    use BatalDenganKonfirmasi;
    use HalamanDataMaster;

    protected function jenisHalaman(): string
    {
        return 'create';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        return static::resourceDataMaster()::simpan($data);
    }
}
