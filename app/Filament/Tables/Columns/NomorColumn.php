<?php

namespace App\Filament\Tables\Columns;

use App\Support\Format;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;

/**
 * Kolom "No." untuk seluruh tabel panel Admin, senada dengan kolom No. tabel Laporan
 * (organisms/laporan-table): rata kanan, warna redup, format angka Indonesia (PRD 15.1, 18.2).
 * Nomor berlanjut antar-halaman pagination: halaman 2 dimulai dari 11.
 *
 *   NomorColumn::make()
 */
class NomorColumn extends TextColumn
{
    public static function make(?string $name = 'nomor'): static
    {
        return parent::make($name ?? 'nomor');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('No.')
            ->rowIndex()
            ->formatStateUsing(fn (string $state): string => Format::angka((int) $state))
            ->alignment(Alignment::End)
            ->color('gray')
            ->width('4rem');
    }
}
