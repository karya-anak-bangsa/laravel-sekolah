<?php

namespace App\Modules\Kesiswaan\Database\Factories;

use App\Modules\Kesiswaan\Enums\Agama;
use App\Modules\Kesiswaan\Enums\Pekerjaan;
use App\Modules\Kesiswaan\Enums\Pendidikan;
use App\Modules\Kesiswaan\Enums\Penghasilan;
use App\Modules\Kesiswaan\Models\WaliSiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WaliSiswa> */
class WaliSiswaFactory extends Factory
{
    protected $model = WaliSiswa::class;

    public function definition(): array
    {
        return [
            'nama_wali_siswa' => fake()->name(),
            'pendidikan' => fake()->randomElement([Pendidikan::Sd, Pendidikan::Smp, Pendidikan::Sma, Pendidikan::Sma, Pendidikan::S1]),
            'pekerjaan' => fake()->randomElement(Pekerjaan::cases()),
            'penghasilan' => fake()->randomElement(Penghasilan::cases()),
            'no_hp' => fake()->numerify('08##########'),
            'agama' => Agama::Islam,
            'alamat' => fake()->address(),
        ];
    }

    public function laki(): static
    {
        return $this->state(['nama_wali_siswa' => fake()->name('male')]);
    }

    public function perempuan(): static
    {
        return $this->state(['nama_wali_siswa' => fake()->name('female')]);
    }
}
