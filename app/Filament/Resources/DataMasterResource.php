<?php

namespace App\Filament\Resources;

use App\Enums\Icon;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
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

    /** Ikon menu dari set ikon aplikasi (sama dengan kartu ringkasan Dashboard). */
    protected static Icon $ikon;

    /** Satu kalimat isi menu, tampil sebagai subjudul halaman daftar. */
    protected static string $deskripsi;

    public static function getNavigationIcon(): Heroicon
    {
        return static::$ikon->heroicon();
    }

    /**
     * Subjudul di bawah judul tiap halaman Data Master. Disusun dari $deskripsi & label model
     * sehingga tiap Resource cukup menulis satu kalimat.
     *
     * @param  'index'|'trash'|'create'|'edit'|'view'  $halaman
     */
    public static function getSubjudul(string $halaman): string
    {
        $label = static::getModelLabel();

        return match ($halaman) {
            'index' => static::$deskripsi,
            'trash' => "Data {$label} yang telah dihapus. Pulihkan untuk mengaktifkannya kembali.",
            'create' => "Lengkapi data {$label} baru. Kolom bertanda * wajib diisi.",
            'edit' => "Perbarui data {$label}. Kolom bertanda * wajib diisi.",
            'view' => "Informasi lengkap data {$label}.",
        };
    }

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
        $diTrash = method_exists($record, 'trashed') && $record->trashed();

        return ! $diTrash && parent::canEdit($record);
    }
}
