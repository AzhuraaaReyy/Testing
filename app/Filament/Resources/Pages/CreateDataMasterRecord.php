<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\BatalDenganKonfirmasi;
use Filament\Resources\Pages\CreateRecord;

/**
 * Halaman Tambah bersama untuk CRUD Siswa, Kelas, dan Guru.
 */
abstract class CreateDataMasterRecord extends CreateRecord
{
    use BatalDenganKonfirmasi;
}
