<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Perilaku bersama Resource Data Master (Siswa, Kelas, Guru):
 * soft delete + Trash (PRD 13), satu grup navigasi, dan label model berbahasa Indonesia.
 */
abstract class DataMasterResource extends Resource
{
    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    /**
     * Bahasa Indonesia tidak memakai bentuk jamak "-s": Siswa, Kelas, Guru.
     */
    public static function getPluralModelLabel(): string
    {
        return static::getModelLabel();
    }

    /**
     * Data di Trash tetap dapat dibuka (Detail) dan dipulihkan.
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    /**
     * Data di Trash tidak dapat diedit; harus dipulihkan dulu agar aturan relasinya terpenuhi (PRD 12.4).
     */
    public static function canEdit(Model $record): bool
    {
        return ! $record->trashed() && parent::canEdit($record);
    }
}
