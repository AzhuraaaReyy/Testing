<?php

namespace App\Actions\Admin;

use App\Enums\Role;
use App\Models\User;

/**
 * Membuat akun Admin. Peran diisi eksplisit (forceCreate) karena `role` sengaja tidak fillable.
 * Password di-hash oleh cast `hashed` pada model User.
 */
final class BuatAdminAction
{
    public function handle(string $nama, string $email, string $password): User
    {
        return User::query()->forceCreate([
            'name' => $nama,
            'email' => $email,
            'password' => $password,
            'role' => Role::Admin,
        ]);
    }
}
