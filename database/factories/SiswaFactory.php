<?php

namespace Database\Factories;

use App\Enums\JenisKelamin;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Siswa>
 */
class SiswaFactory extends Factory
{
    protected $model = Siswa::class;

    public function definition(): array
    {
        $jenisKelamin = fake()->randomElement(JenisKelamin::cases());

        return [
            'nis' => fake()->unique()->numerify('00######'),
            'nama' => fake()->name($jenisKelamin === JenisKelamin::LakiLaki ? 'male' : 'female'),
            'jenis_kelamin' => $jenisKelamin,
            'alamat' => fake()->address(),
            'kelas_id' => Kelas::factory(),
        ];
    }
}
