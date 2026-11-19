<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => Role::Admin,
        ];
    }

    /**
     * Akun portal untuk seorang Siswa.
     */
    public function siswa(?Siswa $siswa = null): static
    {
        return $this->state(fn (): array => [
            'role' => Role::Siswa,
            'siswa_id' => $siswa ?? Siswa::factory(),
        ]);
    }

    /**
     * Akun portal untuk seorang Guru.
     */
    public function guru(?Guru $guru = null): static
    {
        return $this->state(fn (): array => [
            'role' => Role::Guru,
            'guru_id' => $guru ?? Guru::factory(),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
