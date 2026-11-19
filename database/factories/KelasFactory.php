<?php

namespace Database\Factories;

use App\Enums\Tingkat;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        $tingkat = fake()->randomElement(Tingkat::cases());
        $jurusan = fake()->randomElement(['IPA', 'IPS', 'Bahasa']);

        return [
            'nama_kelas' => "{$tingkat->value} {$jurusan} ".fake()->unique()->numberBetween(1, 9999),
            'tingkat' => $tingkat,
            'jurusan' => $jurusan,
            'keterangan' => null,
        ];
    }
}
