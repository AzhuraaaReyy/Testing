<?php

namespace App\Filament\Forms\Components;

use App\Filament\Forms\AturanFormRequest;
use App\Http\Requests\DataMaster\AkunPortalRequest;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Akun login portal Guru/Siswa di form CRUD (PRD 22.2). Disimpan ke relasi `akun`
 * (User dengan role yang dipaksa oleh relasi, bukan oleh input form).
 * - Email kosong = tanpa akun; mengosongkan email pada akun yang ada akan menghapus akunnya.
 * - Password wajib saat akun dibuat; saat edit, kosongkan untuk mempertahankan password lama.
 * - Nama akun selalu mengikuti field "nama" profil.
 */
class AkunPortalSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $namaProfil = fn (array $data, Get $get): array => [...$data, 'name' => $get('nama')];

        $this
            ->heading('Akun Portal')
            ->description('Opsional. Isi untuk memberi akses login portal; kosongkan email untuk menghapus akses.')
            ->relationship('akun', condition: fn (?array $state): bool => filled($state['email'] ?? null))
            ->mutateRelationshipDataBeforeCreateUsing($namaProfil)
            ->mutateRelationshipDataBeforeSaveUsing($namaProfil)
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
                    ->helperText(fn (?User $record): ?string => $record ? 'Kosongkan bila tidak ingin mengubah password.' : null),
            ], fn (?User $record = null, ?Get $get = null): array => AkunPortalRequest::aturan($record, $get?->__invoke('email'))));
    }
}
