<?php

namespace App\Filament\Forms\Components;

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
            ->schema([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->live(onBlur: true),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->minLength(8)
                    ->required(fn (Get $get, ?User $record): bool => filled($get('email')) && $record === null)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (?User $record): ?string => $record ? 'Kosongkan bila tidak ingin mengubah password.' : null),
            ]);
    }
}
