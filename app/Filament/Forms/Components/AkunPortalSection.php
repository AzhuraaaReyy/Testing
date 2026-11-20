<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\AturanFormRequest;
use App\Http\Requests\DataMaster\AkunPortalRequest;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;

/**
 * Akun login portal Guru/Siswa di form CRUD (PRD 22.2). Nilainya dikirim sebagai data["akun"]
 * dan disimpan oleh App\Actions\DataMaster\SinkronAkunPortalAction (sama dengan API):
 * - Email kosong = tanpa akun; mengosongkan email pada akun yang ada akan menghapus akunnya.
 * - Password wajib saat akun dibuat; saat edit, kosongkan untuk mempertahankan password lama.
 * - Nama akun selalu mengikuti field "nama" profil.
 */
class AkunPortalSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->heading('Akun Portal')
            ->description('Opsional. Isi untuk memberi akses login portal; kosongkan email untuk menghapus akses.')
            ->statePath('akun')
            ->columns(2)
            // Aturan validasi: App\Http\Requests\DataMaster\AkunPortalRequest.
            ->schema(AturanFormRequest::terapkan([
                TextInput::make('email')
                    ->label('Email')
                    ->type('email')
                    // Tanda * pada Password ikut berubah begitu email diisi.
                    ->live(onBlur: true),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (?Model $record): string => self::akunDari($record)
                        ? 'Min. 8 karakter, memuat huruf dan angka. Kosongkan bila tidak ingin mengubah password.'
                        : 'Min. 8 karakter, memuat huruf dan angka.'),
            ], fn (?Model $record = null, ?Get $get = null): array => AkunPortalRequest::aturan(self::akunDari($record), $get?->__invoke('email'))));
    }

    /**
     * Akun milik profil yang sedang diedit (null saat Tambah atau profil belum punya akun).
     */
    private static function akunDari(?Model $record): ?User
    {
        return $record instanceof Siswa || $record instanceof Guru ? $record->akun : null;
    }
}
