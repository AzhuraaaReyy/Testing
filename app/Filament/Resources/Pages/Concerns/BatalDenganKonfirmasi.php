<?php

namespace App\Filament\Resources\Pages\Concerns;

use App\Enums\Icon;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

/**
 * Tombol Batal pada form Tambah/Edit (PRD 14.5):
 * - Tanpa perubahan: langsung kembali ke daftar.
 * - Ada perubahan yang belum disimpan: dialog konfirmasi; "Tetap di Halaman" menutup dialog,
 *   "Ya, Batalkan" membuang perubahan lalu kembali ke daftar.
 *
 * Menggantikan dialog bawaan browser yang teks & bahasanya tidak dapat diatur aplikasi.
 * Dialog browser tetap aktif sebagai pengaman untuk navigasi lain (menutup tab, klik menu).
 *
 * @phpstan-require-extends Page
 *
 * @mixin Page
 */
trait BatalDenganKonfirmasi
{
    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label('Batal')
            ->color('gray')
            ->requiresConfirmation(fn (): bool => $this->adaPerubahanBelumDisimpan())
            ->modalIcon(Icon::TriangleAlert->heroicon())
            ->modalHeading('Batalkan perubahan?')
            ->modalDescription('Perubahan yang belum disimpan akan hilang.')
            ->modalSubmitActionLabel('Ya, Batalkan')
            ->modalSubmitAction(fn (Action $action): Action => $action->color('danger'))
            ->modalCancelActionLabel('Tetap di Halaman')
            // Redirect Livewire juga membuat peringatan browser (beforeunload) dilewati Filament.
            ->action(fn () => $this->redirect(static::getResource()::getUrl('index')));
    }

    /**
     * Rumus sama dengan HasUnsavedDataChangesAlert::rememberData() milik Filament, sehingga
     * hasilnya selalu konsisten dengan peringatan perubahan bawaan Filament.
     */
    protected function adaPerubahanBelumDisimpan(): bool
    {
        if (! isset($this->savedDataHash)) {
            return true;
        }

        $dataBersih = (string) str(json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))->replace('\\', '');
        $hashSekarang = hash('sha256', $dataBersih);
        return $hashSekarang !== $this->savedDataHash;
    }
}
