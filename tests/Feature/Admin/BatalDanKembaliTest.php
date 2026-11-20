<?php

use App\Filament\Resources\Siswa\Pages\CreateSiswa;
use App\Filament\Resources\Siswa\Pages\EditSiswa;
use App\Filament\Resources\Siswa\Pages\ViewSiswa;
use App\Filament\Resources\Siswa\SiswaResource;
use App\Models\Siswa;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

/** Tombol Batal ada di komponen aksi form ("form-actions") pada schema konten halaman. */
function aksiBatal(): TestAction
{
    return TestAction::make('cancel')->schemaComponent('form-actions', schema: 'content');
}

/*
| PRD 14.5: Batal tanpa perubahan → langsung ke daftar; dengan perubahan → dialog konfirmasi
| ("Tetap di Halaman" / "Ya, Batalkan").
*/
describe('tombol Batal', function () {
    it('langsung kembali ke daftar bila tidak ada perubahan', function (Closure $halaman) {
        $halaman()
            ->callAction(aksiBatal())
            ->assertRedirect(SiswaResource::getUrl('index'));
    })->with([
        'tambah' => [fn () => Livewire::test(CreateSiswa::class)],
        'edit' => [fn () => Livewire::test(EditSiswa::class, ['record' => Siswa::factory()->create()->id])],
    ]);

    it('meminta konfirmasi berbahasa Indonesia bila ada perubahan', function (Closure $halaman) {
        $aksi = $halaman()
            ->fillForm(['nama' => 'Nama Diubah'])
            ->mountAction(aksiBatal())
            ->assertActionMounted(aksiBatal())
            ->instance()
            ->getMountedAction();

        expect($aksi->getModalHeading())->toBe('Batalkan perubahan?')
            ->and($aksi->getModalDescription())->toBe('Perubahan yang belum disimpan akan hilang.')
            ->and($aksi->getModalSubmitAction()->getLabel())->toBe('Ya, Batalkan')
            ->and($aksi->getModalCancelAction()->getLabel())->toBe('Tetap di Halaman');
    })->with([
        'tambah' => [fn () => Livewire::test(CreateSiswa::class)],
        'edit' => [fn () => Livewire::test(EditSiswa::class, ['record' => Siswa::factory()->create()->id])],
    ]);

    it('membuang perubahan dan kembali ke daftar setelah "Ya, Batalkan"', function () {
        $siswa = Siswa::factory()->create(['nama' => 'Nama Asli']);

        Livewire::test(EditSiswa::class, ['record' => $siswa->id])
            ->fillForm(['nama' => 'Nama Diubah'])
            ->mountAction(aksiBatal())
            ->callMountedAction()
            ->assertRedirect(SiswaResource::getUrl('index'));

        expect($siswa->fresh()->nama)->toBe('Nama Asli');
    });

    it('tetap di halaman dengan isian utuh setelah "Tetap di Halaman"', function () {
        Livewire::test(CreateSiswa::class)
            ->fillForm(['nama' => 'Sedang Diisi'])
            ->mountAction(aksiBatal())
            ->unmountAction()
            ->assertActionNotMounted(aksiBatal())
            ->assertNoRedirect()
            ->assertSet('data.nama', 'Sedang Diisi');
    });
});

describe('tombol Kembali di Detail', function () {
    it('kembali ke daftar', function () {
        $siswa = Siswa::factory()->create();

        Livewire::test(ViewSiswa::class, ['record' => $siswa->id])
            ->assertActionVisible('kembali')
            ->assertActionHasLabel('kembali', 'Kembali')
            ->assertActionHasUrl('kembali', SiswaResource::getUrl('index'));
    });

    it('kembali ke tab Trash untuk data di Trash, tanpa tombol Edit', function () {
        $siswa = Siswa::factory()->create();
        $siswa->delete();

        Livewire::test(ViewSiswa::class, ['record' => $siswa->id])
            ->assertActionHasUrl('kembali', SiswaResource::getUrl('index', ['activeTab' => 'trash']))
            ->assertActionHidden('edit');
    });
});
