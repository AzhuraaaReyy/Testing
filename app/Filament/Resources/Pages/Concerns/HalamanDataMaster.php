<?php

namespace App\Filament\Resources\Pages\Concerns;

use App\Filament\Resources\DataMasterResource;
use Filament\Resources\Pages\Page;
use LogicException;

/**
 * Perilaku bersama halaman Data Master (daftar/tambah/edit/detail): akses ke DataMasterResource
 * untuk subjudul halaman (getSubjudul) dan penyimpanan lewat Action (simpan).
 *
 * @phpstan-require-extends Page
 *
 * @mixin Page
 */
trait HalamanDataMaster
{
    /**
     * @return 'index'|'trash'|'create'|'edit'|'view'
     */
    abstract protected function jenisHalaman(): string;

    public function getSubheading(): string
    {
        return static::resourceDataMaster()::getSubjudul($this->jenisHalaman());
    }

    /**
     * @return class-string<DataMasterResource>
     */
    protected static function resourceDataMaster(): string
    {
        $resource = static::getResource();

        if (! is_subclass_of($resource, DataMasterResource::class)) {
            throw new LogicException(static::class.' harus memakai Resource turunan DataMasterResource.');
        }

        return $resource;
    }
}
