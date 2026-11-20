<?php

namespace App\Filament\Resources\Pages;

use App\Enums\Icon;
use App\Filament\Resources\Pages\Concerns\HalamanDataMaster;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Halaman Detail bersama untuk CRUD Siswa, Kelas, dan Guru (read-only, PRD 5.5, 6.5, 7.5).
 */
abstract class ViewDataMasterRecord extends ViewRecord
{
    use HalamanDataMaster;

    protected function jenisHalaman(): string
    {
        return 'view';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Kembali ke daftar asal: tab Trash untuk data di Trash. Halaman pagination
            // terakhir dipulihkan oleh ListDataMasterRecords (PRD 14.8).
            Action::make('kembali')
                ->label('Kembali')
                ->icon(Icon::ChevronLeft->heroicon())
                ->color('gray')
                ->url(fn (Model $record): string => static::getResource()::getUrl('index', self::diTrash($record) ? ['activeTab' => 'trash'] : [])),
            // Tersembunyi untuk data di Trash (DataMasterResource::canEdit).
            EditAction::make(),
        ];
    }

    private static function diTrash(Model $record): bool
    {
        return method_exists($record, 'trashed') && $record->trashed();
    }
}
