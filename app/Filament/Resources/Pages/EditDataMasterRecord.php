<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\BatalDenganKonfirmasi;
use App\Filament\Resources\Pages\Concerns\HalamanDataMaster;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Halaman Edit bersama untuk CRUD Siswa, Kelas, dan Guru. Hapus sengaja tidak ada di sini:
 * Edit dan Hapus merupakan aksi terpisah di tabel (PRD 14.1).
 */
abstract class EditDataMasterRecord extends EditRecord
{
    use BatalDenganKonfirmasi;
    use HalamanDataMaster;

    protected function jenisHalaman(): string
    {
        return 'edit';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return static::resourceDataMaster()::isiForm($this->getRecord(), $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return static::resourceDataMaster()::simpan($data, $record);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
