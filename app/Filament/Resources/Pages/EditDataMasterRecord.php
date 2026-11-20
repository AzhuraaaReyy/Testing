<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\BatalDenganKonfirmasi;
use App\Filament\Resources\Pages\Concerns\SubjudulDataMaster;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

/**
 * Halaman Edit bersama untuk CRUD Siswa, Kelas, dan Guru. Hapus sengaja tidak ada di sini:
 * Edit dan Hapus merupakan aksi terpisah di tabel (PRD 14.1).
 */
abstract class EditDataMasterRecord extends EditRecord
{
    use BatalDenganKonfirmasi;
    use SubjudulDataMaster;

    protected function jenisHalaman(): string
    {
        return 'edit';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
