<?php

namespace Database\Factories;

use App\Enums\JenisKelamin;
use App\Models\Guru;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guru>
 */
class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        $jenisKelamin = fake()->randomElement(JenisKelamin::cases());

        return [
            'nip' => fake()->boolean(70) ? fake()->unique()->numerify('19##########0#####') : null,
            'nama' => fake()->name($jenisKelamin === JenisKelamin::LakiLaki ? 'male' : 'female'),
            'jenis_kelamin' => $jenisKelamin,
            'alamat' => fake()->address(),
            'no_telepon' => fake()->numerify('08##########'),
        ];
    }
}
