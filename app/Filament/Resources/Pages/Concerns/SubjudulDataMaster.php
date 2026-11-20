<?php

namespace App\Filament\Resources\Pages\Concerns;

use App\Filament\Resources\DataMasterResource;
use Filament\Resources\Pages\Page;
use LogicException;

/**
 * Subjudul di bawah judul halaman Data Master (daftar/tambah/edit/detail), diambil dari
 * DataMasterResource::getSubjudul() agar teksnya didefinisikan di satu tempat.
 *
 * @phpstan-require-extends Page
 *
 * @mixin Page
 */
trait SubjudulDataMaster
{
    /**
     * @return 'index'|'trash'|'create'|'edit'|'view'
     */
    abstract protected function jenisHalaman(): string;

    public function getSubheading(): string
    {
        $resource = static::getResource();

        if (! is_subclass_of($resource, DataMasterResource::class)) {
            throw new LogicException(static::class.' harus memakai Resource turunan DataMasterResource.');
        }

        return $resource::getSubjudul($this->jenisHalaman());
    }
}
